<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    // Clinician email link verification
    public function verify(Request $request, $id, $hash)
    {
        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            if (!hash_equals((string)$hash, sha1($user->email))) {
                return response()->json([
                    'message' => 'Invalid verification link'
                ], 400);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email is already verified'
                ], 200);
            }

            $user->markEmailAsVerified();

            event(new Verified($user));

            return response()->json([
                'message' => 'Email Successfully Verified'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Resend clinician email verification link
    public function resendClinicianVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email is already verified'
                ], 400);
            }

            $signedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email)
                ]
            );

            $user->notify(new VerifyEmailNotification($signedUrl));

            return response()->json([
                'message' => 'A verification link has been sent to your email address'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to resend verification email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
