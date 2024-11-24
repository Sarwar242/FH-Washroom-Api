<?php

namespace App\Http\Controllers;

use App\Models\Toilet;
use App\Models\ToiletUsageLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ToiletController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/toilets/{toilet}/occupy",
     *     summary="Occupy a toilet",
     *     tags={"Toilets"},
     *     security={{ "bearerAuth": {} }},
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
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Toilet not found")
     * )
     */
    private const OCCUPATION_DURATION = 15; // minutes

    public function occupy(Request $request, Toilet $toilet)
    {
        if ($toilet->is_occupied || !$toilet->is_operational) {
            return response()->json([
                'message' => 'Toilet is not available'
            ], 400);
        }

        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes(self::OCCUPATION_DURATION);

        // Create usage log
        ToiletUsageLog::create([
            'toilet_id' => $toilet->id,
            'user_id' => $request->user()->id,
            'started_at' => $now
        ]);

        // Occupy toilet
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

    public function release(Request $request, Toilet $toilet)
    {
        if ($toilet->occupied_by !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to release this toilet'
            ], 403);
        }

        $toilet->release();

        return response()->json([
            'message' => 'Toilet released successfully'
        ]);
    }

    public function extend(Request $request, Toilet $toilet)
    {
        if ($toilet->occupied_by !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to extend occupation'
            ], 403);
        }

        $toilet->update([
            'occupation_expires_at' => Carbon::now()->addMinutes(self::OCCUPATION_DURATION)
        ]);

        return response()->json([
            'message' => 'Occupation time extended successfully',
            'expires_at' => $toilet->occupation_expires_at
        ]);
    }
}
