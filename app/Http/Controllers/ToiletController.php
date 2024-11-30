<?php

namespace App\Http\Controllers;

use App\Models\Toilet;
use App\Models\ToiletUsageLog;
use App\Models\ToiletWaitingList;
use App\Notifications\ToiletNotification;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Toilets",
 *     description="API Endpoints for toilet management"
 * )
 */
class ToiletController extends Controller
{
    private const OCCUPATION_DURATION = 10; // minutes
    private const WAITING_NOTIFICATION_WINDOW = 2; // minutes
    private const REMINDER_BEFORE_EXPIRY = 1; // minutes

    private $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    /**
     * @OA\Post(
     *     path="/toilets/{toilet}/occupy",
     *     summary="Occupy a toilet",
     *     tags={"Toilets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="toilet",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Toilet occupied successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Toilet not available"),
     *     @OA\Response(response=404, description="Toilet not found")
     * )
     */
    public function occupy(Request $request, Toilet $toilet)
    {
        if ($toilet->is_occupied || !$toilet->is_operational) {
            return response()->json(['message' => 'Toilet is not available'], 400);
        }

        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes(self::OCCUPATION_DURATION);

        ToiletUsageLog::create([
            'toilet_id' => $toilet->id,
            'user_id' => $request->user()->id,
            'started_at' => $now
        ]);

        $toilet->update([
            'is_occupied' => true,
            'occupied_by' => $request->user()->id,
            'occupied_at' => $now,
            'occupation_expires_at' => $expiresAt
        ]);

        return response()->json([
            'message' => 'Toilet occupied successfully',
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * @OA\Post(
     *     path="/toilets/{toilet}/release",
     *     summary="Release a toilet",
     *     tags={"Toilets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="toilet",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Toilet released successfully"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to release this toilet"),
     *     @OA\Response(response=404, description="Toilet not found")
     * )
     */
    public function release(Request $request, Toilet $toilet)
    {
        if ($toilet->occupied_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to release this toilet'], 403);
        }

        $toilet->release();

         // Notify waiting users
         $waitingUsers = ToiletWaitingList::where('toilet_id', $toilet->id)
         ->whereNull('notified_at')
         ->orderBy('joined_at')
         ->get();

        foreach ($waitingUsers as $waitingUser) {
            $waitingUser->update([
                'notified_at' => now(),
                'expires_at' => now()->addMinutes(self::WAITING_NOTIFICATION_WINDOW)
            ]);

            $waitingUser->user->notify(new ToiletNotification(
                "Toilet {$toilet->number} is now available. You have {self::WAITING_NOTIFICATION_WINDOW} minutes to occupy it.",
                'availability',
                $toilet->id
            ));

            $this->firebaseService->sendToiletAvailableNotification(
                $waitingUser->user_id,
                $toilet->number,
                $toilet->id
            );
        }

        return response()->json(['message' => 'Toilet released successfully']);
    }

    /**
     * @OA\Post(
     *     path="/toilets/{toilet}/extend",
     *     summary="Extend toilet occupation time",
     *     tags={"Toilets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="toilet",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Occupation time extended successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to extend occupation"),
     *     @OA\Response(response=404, description="Toilet not found")
     * )
     */
    public function extend(Request $request, Toilet $toilet)
    {
        if ($toilet->occupied_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to extend occupation'], 403);
        }

        $toilet->update([
            'occupation_expires_at' => Carbon::now()->addMinutes(self::OCCUPATION_DURATION)
        ]);

        return response()->json([
            'message' => 'Occupation time extended successfully',
            'expires_at' => $toilet->occupation_expires_at
        ]);
    }

     /**
     * @OA\Post(
     *     path="/toilets/{toilet}/join-waitlist",
     *     summary="Join toilet waiting list",
     *     tags={"Toilets"},
     *     @OA\Response(
     *         response=200,
     *         description="Added to waiting list"
     *     )
     * )
     */
    public function joinWaitingList(Request $request, Toilet $toilet)
    {
        $existingEntry = ToiletWaitingList::where('toilet_id', $toilet->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('notified_at')
            ->first();

        if ($existingEntry) {
            return response()->json(['message' => 'Already in waiting list']);
        }

        ToiletWaitingList::create([
            'toilet_id' => $toilet->id,
            'user_id' => $request->user()->id,
            'joined_at' => now(),
            'expires_at' => now()->addHours(1)
        ]);

        return response()->json(['message' => 'Added to waiting list']);
    }

    protected function scheduleExpiryNotification($toilet)
    {
        $notificationTime = $toilet->occupation_expires_at->subMinutes(self::REMINDER_BEFORE_EXPIRY);

        $toilet->occupant->notify((new ToiletNotification(
            "Your occupation of toilet {$toilet->number} will expire in {self::REMINDER_BEFORE_EXPIRY} minute.",
            'expiry_warning',
            $toilet->id
        ))->delay($notificationTime));
    }
}
