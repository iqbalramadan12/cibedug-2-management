<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_users',
        'nis',
        'kelas',
        'jurusan',
        'no_hp',
        'foto',
    ];

    /**
     * Helper URL foto anggota
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users', 'id_users');
    }

    /**
     * Relasi ke model Absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_anggota', 'id_anggota');
    }

    /**
     * Relasi ke model Piket
     */
    public function piket()
    {
        return $this->hasMany(Piket::class, 'id_anggota', 'id_anggota');
    }
}
