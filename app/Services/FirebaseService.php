<?php

namespace App\Services;

use Google\Cloud\Core\ServiceBuilder;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private $projectId;
    private $client;

    public function __construct()
    {
        // Load service account key
        $serviceAccountPath = storage_path('fh-washroom-firebase-firebase-adminsdk-cyqbo-4ca51ed29a.json');
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);

        $this->projectId = $serviceAccount['project_id'];
        
        // Initialize HTTP client with authentication
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $serviceAccount
        );

        $this->client = new \GuzzleHttp\Client([
           'base_uri' => 'https://fcm.googleapis.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $credentials->fetchAuthToken()['access_token'],
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function sendToUser($userId, $title, $body, $data = [])
    {
        try {
            $message = [
                'message' => [
                    'topic' => "user_$userId",
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => $data,
                    'android' => [
                        'priority' => 'high',
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                    ]
                ]
            ];

            $response = $this->client->post("projects/{$this->projectId}/messages:send", [
                'json' => $message
            ]);
            Log::info("res: ", json_decode($response->getBody()->getContents(), true));
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Firebase notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendToiletAvailableNotification($userId, $toiletNumber, $toiletId)
    {
        return $this->sendToUser(
            $userId,
            'Toilet Available',
            "Toilet $toiletNumber is now available for use",
            [
                'type' => 'toilet_available',
                'toilet_id' => (string)$toiletId // FCM data values must be strings
            ]
        );
    }

    public function sendExpiryWarningNotification($userId, $toiletNumber, $minutesLeft)
    {
        return $this->sendToUser(
            $userId,
            'Session Expiring Soon',
            "Your session for Toilet $toiletNumber will expire in $minutesLeft minutes",
            [
                'type' => 'session_expiring',
                'minutes_left' => (string)$minutesLeft
            ]
        );
    }
}
