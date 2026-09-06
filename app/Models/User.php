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
     * registration. Keeping this as a static list (rather than a separate
     * DB table) keeps things simple for a small team.
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
