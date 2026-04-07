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

    protected static function booted()
    {
        static::updated(function ($booking) {
            // لو الحالة اتغيرت
            if ($booking->isDirty('status')) {
                BookingStatusLog::create([
                    'booking_id' => $booking->id,
                    'status'     => $booking->status,
                    'note'       => 'Updated from Booking Screen',
                ]);
            }
        });

        // Optional: لو عايز تسجيل إنشاء أولية كمان
        static::created(function ($booking) {
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'status'     => $booking->status,
                'note'       => 'Booking created',
            ]);
        });
    }

    public function logs()
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function services()
{
    return $this->hasMany(BookingService::class);
}

public function extensions()
{
    return $this->hasMany(BookingExtension::class);
}

public function totalPrice()
{
    $servicesPrice   = $this->services->sum(fn($s) => $s->totalPrice());
    $extensionsPrice = $this->extensions->sum('price');

    return $this->price + $servicesPrice + $extensionsPrice;
}
}
