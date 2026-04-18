<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'job_title',
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

    public function hasJobTitle(string ...$titles): bool
    {
        $jobTitle = strtolower($this->job_title ?? '');

        return in_array($jobTitle, array_map('strtolower', $titles), true);
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
