<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke model Anggota
     */
    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'id_users', 'id_users');
    }

    /**
     * Relasi ke model Kas
     */
    public function kas()
    {
        return $this->hasMany(Kas::class, 'id_users', 'id_users');
    }

    /**
     * Relasi ke model Pengumuman
     */
    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'id_users', 'id_users');
    }

    /**
     * Helper cek role
     */
    public function isAdmin(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'admin';
    }

    public function isGuru(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'guru';
    }

    public function isUser(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'user';
    }
}
