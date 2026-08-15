<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'string', 'in:web,android,ios'],
        ]);

        $request->user()->deviceTokens()->updateOrCreate(
            ['token' => $request->input('token')],
            ['platform' => $request->input('platform', 'web')]
        );

        return response()->json(['message' => 'Device registered.']);
    }

    public function destroy(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);

        $request->user()->deviceTokens()->where('token', $request->input('token'))->delete();

        return response()->json(['message' => 'Device unregistered.']);
    }
}