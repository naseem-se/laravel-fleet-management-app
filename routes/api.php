<?php

use App\Http\Controllers\Api\V1\Admin\CompanyController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionPlanController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanySettingsController;
use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\FuelEntryController;
use App\Http\Controllers\Api\V1\JourneyController;
use App\Http\Controllers\Api\V1\MaintenanceRecordController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\VehicleController;
use App\Http\Controllers\Api\V1\VehicleDocumentController;
use App\Http\Controllers\Api\V1\VehicleQrController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\DriverDocumentController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:login');
    Route::post('/auth/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'changePassword']);

        Route::post('device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::middleware('subscription.active')->group(function () {

            Route::middleware('role:company_admin|dispatcher')->group(function () {
                Route::apiResource('vehicles', VehicleController::class);
                Route::get('vehicles/{vehicle}/history', [VehicleController::class, 'history']);
                Route::post('vehicles/{vehicle}/documents', [VehicleDocumentController::class, 'store'])->middleware('throttle:uploads');
                Route::put('vehicle-documents/{vehicleDocument}', [VehicleDocumentController::class, 'update'])->middleware('throttle:uploads');
                Route::delete('vehicle-documents/{vehicleDocument}', [VehicleDocumentController::class, 'destroy']);
                Route::get('documents/expiring', [VehicleDocumentController::class, 'expiring']);

                Route::apiResource('drivers', DriverController::class);
                Route::post('drivers/{driver}/login', [DriverController::class, 'createLogin']);
                Route::put('drivers/{driver}/login', [DriverController::class, 'updateLogin']);

                Route::get('journeys/live', [JourneyController::class, 'live']);
                Route::get('fuel-entries', [FuelEntryController::class, 'index']);
                Route::post('fuel-entries/manual', [FuelEntryController::class, 'storeAdmin'])->middleware('throttle:uploads');
                Route::put('fuel-entries/{fuelEntry}', [FuelEntryController::class, 'update']);
                Route::delete('fuel-entries/{fuelEntry}', [FuelEntryController::class, 'destroy']);

                Route::apiResource('maintenance-records', MaintenanceRecordController::class);
                Route::get('maintenance/upcoming', [MaintenanceRecordController::class, 'upcoming']);

                Route::get('company/settings', [CompanySettingsController::class, 'show']);
                Route::put('company/settings', [CompanySettingsController::class, 'update']);

                Route::post('drivers/{driver}/documents', [DriverDocumentController::class, 'store']);
                Route::put('driver-documents/{driverDocument}', [DriverDocumentController::class, 'update']);
                Route::delete('driver-documents/{driverDocument}', [DriverDocumentController::class, 'destroy']);
                Route::get('driver-documents/expiring', [DriverDocumentController::class, 'expiring']);

                Route::delete('journeys/{journey}', [JourneyController::class, 'destroy']);

                Route::middleware('throttle:reports')->group(function () {
                    Route::get('reports/overview', [ReportController::class, 'overview']);
                    Route::get('reports/vehicle/{vehicle}', [ReportController::class, 'vehicle']);
                    Route::get('reports/driver/{driver}', [ReportController::class, 'driver']);
                    Route::get('reports/fuel', [ReportController::class, 'fuel']);
                    Route::get('reports/maintenance', [ReportController::class, 'maintenance']);
                    Route::get('reports/fleet-summary', [ReportController::class, 'fleetSummary']);
                });
            });

            Route::middleware('role:driver')->group(function () {
                Route::get('vehicles/qr/{qrToken}', [VehicleQrController::class, 'resolve']);

                Route::post('journeys/start', [JourneyController::class, 'start'])->middleware('throttle:uploads');
                Route::post('journeys/{journey}/ping', [JourneyController::class, 'ping'])->middleware('throttle:journey-ping');
                Route::post('journeys/{journey}/end', [JourneyController::class, 'end'])->middleware('throttle:uploads');
                Route::get('journeys/current', [JourneyController::class, 'current']);

                Route::post('fuel-entries', [FuelEntryController::class, 'store'])->middleware('throttle:uploads');
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

            Route::get('companies/{company}/activity', [CompanyController::class, 'activity']);
        });
    });

    Route::middleware(['auth:sanctum', 'throttle:login'])->group(function () {
        Route::post('/auth/2fa/setup', [\App\Http\Controllers\Api\V1\TwoFactorController::class, 'setup']);
        Route::post('/auth/2fa/confirm', [\App\Http\Controllers\Api\V1\TwoFactorController::class, 'confirm']);
        Route::post('/auth/2fa/verify', [\App\Http\Controllers\Api\V1\TwoFactorController::class, 'verify'])->middleware('ability:2fa-pending');
    });
});