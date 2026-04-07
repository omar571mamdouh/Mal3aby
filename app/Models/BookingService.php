<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    protected $fillable = [
        'booking_id', 'name', 'pricing_type', 'price', 'quantity', 'notes'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function totalPrice()
    {
        return $this->price * $this->quantity;
    }
}