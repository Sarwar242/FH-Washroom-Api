<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceReportRequest;
use App\Models\MaintenanceReport;
use App\Models\Toilet;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Maintenance",
 *     description="API Endpoints for maintenance management"
 * )
 */
class MaintenanceReportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/maintenance/report",
     *     summary="Submit a maintenance report",
     *     tags={"Maintenance"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"toilet_id", "issue_type", "description", "priority"},
     *             @OA\Property(property="toilet_id", type="integer"),
     *             @OA\Property(property="issue_type", type="string", enum={"plumbing", "cleaning", "repair", "other"}),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report submitted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceReport")
     *     )
     * )
     */
    public function report(MaintenanceReportRequest $request)
    {
        $report = MaintenanceReport::create([
            'toilet_id' => $request->toilet_id,
            'reported_by' => Auth::id(),
            'issue_type' => $request->issue_type,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        if ($request->priority === 'urgent') {
            $report->toilet->update(['is_operational' => false]);
        }

        return response()->json([
            'message' => 'Maintenance report submitted successfully',
            'report' => $report
        ]);
    }

    /**
     * @OA\Get(
     *     path="/maintenance/reports",
     *     summary="Get all active maintenance reports",
     *     tags={"Maintenance"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance reports",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MaintenanceReport")
     *         )
     *     )
     * )
     */
    public function getReports()
    {
        $reports = MaintenanceReport::with(['toilet.washroom'])
            ->where('status', '!=', 'resolved')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }

    /**
     * @OA\Get(
     *     path="/maintenance/history/{toilet}",
     *     summary="Get maintenance history for a specific toilet",
     *     tags={"Maintenance"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="toilet",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance history",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MaintenanceReport")
     *         )
     *     )
     * )
     */
    public function getToiletHistory(Toilet $toilet)
    {
        $history = MaintenanceReport::where('toilet_id', $toilet->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
