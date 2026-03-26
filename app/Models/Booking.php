<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'court_id',
        'booking_date',
        'time_slot_id',
        'price',
        'status',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(CourtTimeSlot::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function cancellation()
    {
        return $this->hasOne(Cancellation::class);
    }
}
