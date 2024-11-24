<?php

namespace App\Http\Controllers;

use App\Models\Washroom;
use Illuminate\Http\Request;

class WashroomController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/washrooms",
     *     summary="Get all washrooms with their toilets",
     *     tags={"Washrooms"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="List of washrooms",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="floor", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"male", "female", "unisex"}),
     *                 @OA\Property(property="is_operational", type="boolean"),
     *                 @OA\Property(property="available_toilets", type="integer"),
     *                 @OA\Property(
     *                     property="toilets",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="number", type="string"),
     *                         @OA\Property(property="is_occupied", type="boolean"),
     *                         @OA\Property(property="occupied_by", type="string", nullable=true),
     *                         @OA\Property(property="time_remaining", type="integer", nullable=true)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        $washrooms = Washroom::with(['toilets' => function ($query) {
            $query->where('is_operational', true);
        }])->get()->map(function ($washroom) {
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
        });

        return response()->json($washrooms);
    }
}
