<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\PatientOtp;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class PatientAuthController extends Controller
{
     // Verify patient OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|min:8|confirmed',
        ]);

         // Find patient (get the most recent patient record for this email)
    try {
        $patient = Patient::where('email', $request->email)->latest('id')->first();

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error finding patient.',
            'error' => $e->getMessage()
        ], 500);
    }

    if (!$patient) {
        return response()->json([
            'message' => 'Patient not found.'
        ], 404);
    }

    // Find latest OTP
    try {
        $patientOtp = PatientOtp::where('patient_id', $patient->id)->latest('id')->first();

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error finding OTP.',
            'error' => $e->getMessage()
        ], 500);
    }   

    if (!$patientOtp || $patientOtp->verified_at !== null) {
        return response()->json([
            'message' => 'Invalid or already used OTP.'
        ], 400);
    }

    if (now()->greaterThan($patientOtp->expires_at)) {
        return response()->json([
            'message' => 'OTP has expired.'
        ], 400);
    }

    // Verify OTP match
    if (!Hash::check((string)$request->otp, $patientOtp->otp)) {
        $patientOtp->increment('attempts');
        return response()->json([
            'message' => 'Invalid OTP.'
        ], 400);
    }

    // Mark OTP as verified
    $patientOtp->verified_at = now();
    try {
        $patientOtp->save();

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error updating OTP.',
            'error' => $e->getMessage()
        ], 500);
    }

    // Ensure patient role exists
    $role = Role::firstOrCreate(['name' => 'patient']);

    // Create or update User for patient
    try {
        $user = User::where('email', $patient->email)->first();
        if (!$user) {
            $user = new User();
            $user->role_id = $role->id;
            $user->name = $patient->name;
            $user->email = $patient->email;
        }
        $user->password = Hash::make($request->password);
        $user->email_verified_at = now();
        $user->save();

        // Activate patient and link to User account
        $patient->user_id = $user->id;
        $patient->email_verified_at = now();
        $patient->phone_verified_at = now();
        $patient->status = 'active';
        $patient->save();

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error setting up patient account.',
            'error' => $e->getMessage()
        ], 500);
    }

    return response()->json([
        'message' => 'Account setup and OTP verified successfully. You can now log in.',
        'patient' => $patient,
    ], 200);
}
    // Patient login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user
        try {
            $user = User::where('email', $request->email)->first();

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding user.',
                'error' => $e->getMessage()
            ], 500);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Confirm user is a patient
        if (!$user->role || $user->role->name !== 'patient') {
            return response()->json([
                'message' => 'This account is not registered as a patient.'
            ], 403);
        }

        // Find patient's profile
        try {
            $patient = Patient::where('user_id', $user->id)->first();

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding patient profile.',
                'error' => $e->getMessage()
            ], 500);
        }

        if (!$patient || $patient->status !== 'active') {
            return response()->json([
                'message' => 'Please complete your account verification before logging in.'
            ], 403);
        }

        $token = $user->createToken('patient-auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'user' => $user,
            'patient' => $patient,
            'role' => $user->role,
            'abilities' => $user->abilities(),
            'token' => $token,
        ], 200);
    }

    // Resend patient OTP
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $patient = Patient::where('email', $request->email)
                ->latest('id')
                ->first();

            if (!$patient) {
                return response()->json([
                    'message' => 'Patient not found'
                ], 404);
            }

            if ($patient->email_verified_at && $patient->phone_verified_at) {
                return response()->json([
                    'message' => 'Patient is already verified'
                ], 400);
            }

            $patient->sendOtp();

            return response()->json([
                'message' => 'Verification code successfully sent to your email.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to resend verification code',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
