<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    use HasFactory;

    protected $table = 'lapangans'; // Nama tabel di database

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga_per_jam',
        'tersedia'
    ];
}
