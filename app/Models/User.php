<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'job_title',
        'role',
        'organization',
    ];

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

    public function hasRole(string ...$titles): bool
    {
        $role = strtolower($this->role ?? '');

        return in_array($role, array_map('strtolower', $titles), true);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function administrations(): HasMany
    {
        return $this->hasMany(Administration::class);
    }

    public function prescribedPrescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'prescriber_id');
    }

    public function createdPrescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'created_by_user_id');
    }
}
