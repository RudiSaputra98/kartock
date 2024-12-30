<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;


    protected $table = 'user_logs';  // Tabel yang digunakan


    protected $fillable = [
        'user_id',
        'activity_type',
        'entity',
        'description',
        'data'
    ];



    protected $casts = [
        'data' => 'array', // Untuk memudahkan data JSON
    ];

    protected static function booted()
    {
        static::creating(function ($log) {
            // Cek jumlah log dan hapus log lama jika lebih dari 100
            if (self::count() >= 15) {
                self::orderBy('created_at', 'asc')->first()->delete();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
