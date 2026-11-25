<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';
    protected $primaryKey = 'id_pengaturan';

    protected $fillable = [
        'kunci',
        'nilai',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan kunci
     */
    public static function get(string $kunci, ?string $default = null): ?string
    {
        $setting = static::where('kunci', $kunci)->first();
        return $setting && $setting->nilai !== null ? $setting->nilai : $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan
     */
    public static function set(string $kunci, ?string $nilai): static
    {
        return static::updateOrCreate(
            ['kunci' => $kunci],
            ['nilai' => $nilai]
        );
    }
}
