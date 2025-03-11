<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'lapangan_id',
        'user_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'status'
    ];

    // Relasi ke model Lapangan
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
