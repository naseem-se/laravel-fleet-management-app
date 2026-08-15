<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function setup(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $request->user()->update(['two_factor_secret' => Crypt::encryptString($secret)]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $request->user()->email,
            $secret
        );

        return response()->json(['qr_code_url' => $qrCodeUrl, 'secret' => $secret]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $secret = Crypt::decryptString($user->two_factor_secret);

        $valid = (new Google2FA())->verifyKey($secret, $request->input('code'));

        if (! $valid) {
            throw ValidationException::withMessages(['code' => ['Invalid code.']]);
        }

        $user->update(['two_factor_enabled' => true]);

        return response()->json(['message' => '2FA enabled.']);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $secret = Crypt::decryptString($user->two_factor_secret);

        $valid = (new Google2FA())->verifyKey($secret, $request->input('code'));

        if (! $valid) {
            throw ValidationException::withMessages(['code' => ['Invalid code.']]);
        }

        return response()->json(['message' => 'Verified.']);
    }
}