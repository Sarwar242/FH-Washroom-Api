<?php
// routes/api.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WashroomController;
use App\Http\Controllers\ToiletController;
use App\Http\Controllers\MaintenanceReportController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Washroom routes
    Route::get('/washrooms', [WashroomController::class, 'index']);
    Route::get('/washrooms/{washroom}', [WashroomController::class, 'show']);
    Route::get('/washrooms/floor/{floor}', [WashroomController::class, 'byFloor']);

    // Toilet routes
    Route::post('/toilets/{toilet}/occupy', [ToiletController::class, 'occupy']);
    Route::post('/toilets/{toilet}/release', [ToiletController::class, 'release']);
    Route::post('/toilets/{toilet}/extend', [ToiletController::class, 'extend']);

    // Maintenance routes
    Route::post('/maintenance/report', [MaintenanceReportController::class, 'report']);
    Route::get('/maintenance/reports', [MaintenanceReportController::class, 'getReports']);
    Route::get('/maintenance/history/{toilet}', [MaintenanceReportController::class, 'getToiletHistory']);

    //waiting list
    Route::post('/toilets/{toilet}/join-waitlist', [ToiletController::class, 'joinWaitingList']);

    //Notification
    Route::middleware('auth:sanctum')->post('/notifications/register-token', [NotificationController::class, 'registerToken']);
});
