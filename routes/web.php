<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/up', fn () => response('OK'));
Route::get('/health', [\App\Http\Controllers\HealthController::class, 'check']);

Route::get('/email/verify/{id}/{hash}', function (string $id, string $hash) {
    if (! URL::hasValidSignature(request())) {
        abort(403, 'Invalid or expired verification link.');
    }

    $user = User::withoutGlobalScopes()->find($id);

    if (! $user || ! hash_equals(sha1($user->email), $hash)) {
        abort(403, 'Invalid verification link.');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    $frontendUrl = $user->hasRole('driver')
        ? config('app.driver_frontend_url')
        : ($user->company_id === null ? config('app.super_admin_frontend_url') : config('app.admin_frontend_url'));

    return redirect("{$frontendUrl}/email-verified");
})->middleware(['signed'])->name('verification.verify');