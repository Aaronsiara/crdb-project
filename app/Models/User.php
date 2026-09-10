<?php

namespace App\Models;

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
        'position',
        'avatar_path',
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

    /**
     * Public URL for this user's profile photo, or null if they don't have
     * one. Photos are stored on the "public" disk (storage/app/public/...),
     * which requires `php artisan storage:link` to be web-accessible.
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/' . $this->avatar_path) : null;
    }

    /**
     * The fixed list of Data Department positions selectable at
     * registration / profile editing.
     */
    public static function positions(): array
    {
        return [
            'Data Analyst',
            'Data Engineer',
            'Data Scientist',
            'Database Administrator',
            'Business Intelligence Officer',
            'Data Department Team Lead',
        ];
    }
}
