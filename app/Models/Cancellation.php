<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{
    protected $fillable = [
        'booking_id',
        'reason',
        'cancelled_at',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}