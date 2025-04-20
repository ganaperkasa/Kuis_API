<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'location', 'photo', 'capacity', 'type', 'status'
    ];

    public function reservations()
{
    return $this->hasMany(Reservation::class);
}

}
