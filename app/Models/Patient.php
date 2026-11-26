<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
     protected $fillable = [
        'user_id',
        'clinician_id',
        'name',
        'email',
        'phone',
        'medication_information',
        'therapy_start_date',
        'email_verified_at',
        'phone_verified_at',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinician()
    {
        return $this->belongsTo(Clinician::class);
    }

    public function moodEntries()
    {
    return $this->hasMany(MoodEntry::class);
    }

    public function otps()
    {
        return $this->hasMany(PatientOtp::class);
    }

    public function sendOtp(): int
    {
        $otp = random_int(100000, 999999);

        $patientOtp = new PatientOtp();
        $patientOtp->patient_id = $this->id;
        $patientOtp->otp = \Illuminate\Support\Facades\Hash::make((string)$otp);
        $patientOtp->expires_at = now()->addMinutes(10);
        $patientOtp->attempts = 0;
        $patientOtp->save();

        \Illuminate\Support\Facades\Mail::raw(
            "Your MoodBridge verification code is: {$otp}. This code expires in 10 minutes.",
            function ($message) {
                $message->to($this->email)->subject('MoodBridge Verification Code');
            }
        );

        return $otp;
    }
}
