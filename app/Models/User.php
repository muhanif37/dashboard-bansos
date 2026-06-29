<?php

namespace App\Models;

use App\Models\RealisasiBansos;
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
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'aktif'             => 'boolean',
        ];
    }

    // =========================================================
    // Helpers
    // =========================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function targetBansos()
    {
        return $this->hasMany(TargetBansos::class, 'created_by');
    }

    public function realisasiBansos()
    {
        return $this->hasMany(RealisasiBansos::class, 'created_by');
    }

    public function stagingImport()
    {
        return $this->hasMany(StagingImport::class, 'imported_by');
    }
}