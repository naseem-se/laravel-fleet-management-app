<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::with(['company', 'driver'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive. Contact your administrator.'],
            ]);
        }

        // Company-side checks (super admins have company_id === null and skip these)
        if ($user->company_id !== null) {
            if ($user->company->status === 'suspended') {
                throw ValidationException::withMessages([
                    'email' => ['Your company account has been suspended.'],
                ]);
            }
        }

        $user->forceFill(['last_login_at' => now()])->save();

        // Token abilities mirror the role — lets a future API consumer (or the
        // frontend) reason about capability without a second roles round-trip,
        // and lets us scope a specific token narrowly if we add API keys later.
        $abilities = $user->getRoleNames()->toArray() ?: ['*'];

        $token = $user->createToken(
            name: $request->userAgent() ?? 'api-token',
            abilities: $abilities
        )->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me()
    {
        return new UserResource(request()->user()->load(['company', 'driver']));
    }
}