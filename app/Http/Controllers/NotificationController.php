<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Notification",
 *     description="API Endpoints for Notifications"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/notifications/register-token",
     *     summary="Submit a maintenance report",
     *     tags={"Notification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "device_type"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="device_type", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Device token saved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Notification")
     *     )
     * )
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'required|in:ios,android'
        ]);

        DeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token' => $request->token
            ],
            [
                'device_type' => $request->device_type
            ]
        );

        return response()->json(['message' => 'Token registered successfully']);
    }
}
