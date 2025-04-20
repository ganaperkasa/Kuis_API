<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Lapangan;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'lapangan_id',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
    ];

    /**
     * Get the user who made the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lapangan (field) that was reserved.
     */
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }
}
