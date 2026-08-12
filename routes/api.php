<?php

use App\Http\Controllers\Api\V1\Admin\CompanyController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionPlanController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\FuelEntryController;
use App\Http\Controllers\Api\V1\JourneyController;
use App\Http\Controllers\Api\V1\MaintenanceRecordController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\VehicleController;
use App\Http\Controllers\Api\V1\VehicleDocumentController;
use App\Http\Controllers\Api\V1\VehicleQrController;
use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::middleware('subscription.active')->group(function () {

            Route::middleware('role:company_admin|dispatcher')->group(function () {
                Route::apiResource('vehicles', VehicleController::class);
                Route::get('vehicles/{vehicle}/history', [VehicleController::class, 'history']);
                Route::post('vehicles/{vehicle}/documents', [VehicleDocumentController::class, 'store']);
                Route::delete('vehicle-documents/{vehicleDocument}', [VehicleDocumentController::class, 'destroy']);
                Route::get('documents/expiring', [VehicleDocumentController::class, 'expiring']);

                Route::apiResource('drivers', DriverController::class);

                Route::get('journeys/live', [JourneyController::class, 'live']);
                Route::get('fuel-entries', [FuelEntryController::class, 'index']);

                Route::apiResource('maintenance-records', MaintenanceRecordController::class);
                Route::get('maintenance/upcoming', [MaintenanceRecordController::class, 'upcoming']);

                Route::get('reports/vehicle/{vehicle}', [ReportController::class, 'vehicle']);
                Route::get('reports/driver/{driver}', [ReportController::class, 'driver']);
                Route::get('reports/fuel', [ReportController::class, 'fuel']);
                Route::get('reports/maintenance', [ReportController::class, 'maintenance']);
                Route::get('reports/fleet-summary', [ReportController::class, 'fleetSummary']);

                Route::post('drivers/{driver}/login', [DriverController::class, 'createLogin']);
                Route::put('drivers/{driver}/login', [DriverController::class, 'updateLogin']);
                Route::post('fuel-entries/manual', [FuelEntryController::class, 'storeAdmin']);
                Route::get('reports/overview', [ReportController::class, 'overview']);
                Route::put('vehicle-documents/{vehicleDocument}', [VehicleDocumentController::class, 'update']);
            });

            Route::middleware('role:driver')->group(function () {
                Route::get('vehicles/qr/{qrToken}', [VehicleQrController::class, 'resolve']);

                Route::post('journeys/start', [JourneyController::class, 'start']);
                Route::post('journeys/{journey}/ping', [JourneyController::class, 'ping']);
                Route::post('journeys/{journey}/end', [JourneyController::class, 'end']);
                Route::get('journeys/current', [JourneyController::class, 'current']);

                Route::post('fuel-entries', [FuelEntryController::class, 'store']);
                Route::get('fuel-entries/mine', [FuelEntryController::class, 'mine']);
            });

            Route::middleware('role:company_admin|dispatcher|driver')->group(function () {
                Route::get('journeys/{journey}', [JourneyController::class, 'show']);
                Route::get('drivers/{driver}/performance', [DriverController::class, 'performance']);
            });
        });

        Route::prefix('admin')->middleware('role:super_admin')->group(function () {
            Route::get('stats', [CompanyController::class, 'stats']);

            Route::apiResource('companies', CompanyController::class)->except(['destroy']);
            Route::post('companies/{company}/suspend', [CompanyController::class, 'suspend']);
            Route::post('companies/{company}/activate', [CompanyController::class, 'activate']);

            Route::get('companies/{company}/subscriptions', [SubscriptionController::class, 'index']);
            Route::post('companies/{company}/subscriptions', [SubscriptionController::class, 'store']);

            Route::apiResource('subscription-plans', SubscriptionPlanController::class)->except(['show']);
        });
    });
});