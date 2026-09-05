<?php

namespace App\Models;

// Laravel generates this file by default with Notifiable + Authenticatable
// already wired up. This is the same file with one addition: isAdmin() and
// the `role` field made fillable/hidden appropriately.

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function segmentationRuns()
    {
        return $this->hasMany(SegmentationRun::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
