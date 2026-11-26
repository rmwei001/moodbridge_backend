<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientOtp extends Model
{
    protected $fillable = [
        'patient_id',
        'otp',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}