<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
     // A user belongs to one role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function clinician()
    {
        return $this->hasOne(Clinician::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function abilities()
    {
        if ($this->role->name == 'clinician') {
            return [
                'patients:create',
                'patients:view-assigned',
                'patients:update',
                'patients:view-mood-updates',
            ];
        }

        if ($this->role->name == 'patient') {
            return [
                'profile:view-own',
                'mood-entries:create',
                'mood-entries:view-own',
                'profile:update-own',
            ];
        }

        return [];
    }
}
