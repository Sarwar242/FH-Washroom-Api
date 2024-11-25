<?php

namespace App\Http\Controllers;

use App\Models\Washroom;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Washrooms",
 *     description="API Endpoints for washroom management"
 * )
 */
class WashroomController extends Controller
{
    /**
     * @OA\Get(
     *     path="/washrooms",
     *     summary="Get all washrooms with their toilets",
     *     tags={"Washrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of washrooms",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Washroom")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $washrooms = Washroom::with(['toilets' => function ($query) {
            $query->where('is_operational', true);
        }])->get()->map(function ($washroom) {
            return $this->formatWashroom($washroom);
        });

        return response()->json($washrooms);
    }

    /**
     * @OA\Get(
     *     path="/washrooms/{washroom}",
     *     summary="Get specific washroom details",
     *     tags={"Washrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="washroom",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Washroom details",
     *         @OA\JsonContent(ref="#/components/schemas/Washroom")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Washroom not found"
     *     )
     * )
     */
    public function show(Washroom $washroom)
    {
        $washroom->load(['toilets' => function ($query) {
            $query->where('is_operational', true);
        }]);

        return response()->json($this->formatWashroom($washroom));
    }

    /**
     * @OA\Get(
     *     path="/washrooms/floor/{floor}",
     *     summary="Get washrooms by floor",
     *     tags={"Washrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="floor",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of washrooms on the specified floor",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Washroom")
     *         )
     *     )
     * )
     */
    public function byFloor($floor)
    {
        $washrooms = Washroom::where('floor', $floor)
            ->with(['toilets' => function ($query) {
                $query->where('is_operational', true);
            }])
            ->get()
            ->map(function ($washroom) {
                return $this->formatWashroom($washroom);
            });

        return response()->json($washrooms);
    }

    /**
     * Helper function to format washroom data consistently
     */
    private function formatWashroom(Washroom $washroom)
    {
        return [
            'id' => $washroom->id,
            'name' => $washroom->name,
            'floor' => $washroom->floor,
            'type' => $washroom->type,
            'is_operational' => $washroom->is_operational,
            'available_toilets' => $washroom->getAvailableToiletsCount(),
            'total_toilets' => $washroom->toilets->count(),
            'toilets' => $washroom->toilets->map(function ($toilet) {
                return [
                    'id' => $toilet->id,
                    'number' => $toilet->number,
                    'is_occupied' => $toilet->is_occupied,
                    'occupied_by' => $toilet->occupant ? $toilet->occupant->name : null,
                    'time_remaining' => $toilet->occupation_expires_at ?
                        now()->diffInMinutes($toilet->occupation_expires_at, false) : null
                ];
            })
        ];
    }
}
