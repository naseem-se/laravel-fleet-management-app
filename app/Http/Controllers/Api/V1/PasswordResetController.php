<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            $frontendUrl = $user->company_id === null
                ? config('app.super_admin_frontend_url')
                : config('app.admin_frontend_url');

            $user->notify(new ResetPasswordNotification($token, $frontendUrl));
        }

        // Same response whether or not the email exists — prevents account enumeration.
        return response()->json([
            'message' => 'If an account with that email exists, a password reset link has been sent.',
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->validated('email'))
            ->first();

        if (! $record || ! Hash::check($request->validated('token'), $record->token)) {
            return response()->json(['message' => 'This password reset link is invalid.'], 422);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->validated('email'))->delete();
            return response()->json(['message' => 'This password reset link has expired. Please request a new one.'], 422);
        }

        $user = User::where('email', $request->validated('email'))->first();

        if (! $user) {
            return response()->json(['message' => 'This password reset link is invalid.'], 422);
        }

        $user->forceFill(['password' => Hash::make($request->validated('password'))])->save();

        DB::table('password_reset_tokens')->where('email', $request->validated('email'))->delete();

        // Log out everywhere — a password reset is a security event.
        $user->tokens()->delete();

        return response()->json(['message' => 'Your password has been reset. Please log in.']);
    }
}