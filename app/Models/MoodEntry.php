<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodEntry extends Model
{
     protected $fillable = [
        'patient_id',
        'mood_rating',
        'reflection',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
