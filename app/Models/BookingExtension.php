<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingExtension extends Model
{
    protected $fillable = [
        'booking_id',
        'extra_minutes',
        'price',
        'approved_by',
        'notes',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}