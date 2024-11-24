<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceReportRequest;
use App\Models\MaintenanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceReportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/maintenance/report",
     *     summary="Submit a maintenance report",
     *     tags={"Maintenance"},
     *     security={{ "bearerAuth": {} }},
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
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="report",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="toilet_id", type="integer"),
     *                 @OA\Property(property="issue_type", type="string"),
     *                 @OA\Property(property="priority", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
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

        // Mark toilet as non-operational for urgent issues
        if ($request->priority === 'urgent') {
            $report->toilet->update(['is_operational' => false]);
        }

        return response()->json([
            'message' => 'Maintenance report submitted successfully',
            'report' => $report
        ]);
    }

    public function getReports()
    {
        $reports = MaintenanceReport::with(['toilet.washroom'])
            ->where('status', '!=', 'resolved')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }
}
