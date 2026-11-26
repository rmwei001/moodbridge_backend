<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinician extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'license_number',
    ];

    // A clinician belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // A clinician can have many patients
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
}
