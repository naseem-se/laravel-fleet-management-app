<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        // Keep a linked driver record's own name/phone in sync, so the
        // profile a driver edits and the driver record an admin sees
        // never drift apart from each other.
        if ($user->driver) {
            $user->driver->update([
                'name' => $user->name,
                'phone' => $user->phone ?? $user->driver->phone,
            ]);
        }

        return new UserResource($user->fresh(['company', 'driver']));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->forceFill(['password' => Hash::make($request->validated('password'))])->save();

        // Password changed — revoke every other session, keep only the
        // token used for this very request active.
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json(['message' => 'Password updated. You have been signed out of other devices.']);
    }
}