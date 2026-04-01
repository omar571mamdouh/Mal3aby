<?php
// app/Observers/BookingObserver.php

namespace App\Observers;

use App\Models\Booking;
use App\Models\BookingStatusLog;

class BookingObserver
{
   // app/Observers/BookingObserver.php

public function updating(Booking $booking): void
{
    if ($booking->isDirty('status')) {
        // Booking Status Log
        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'status'     => $booking->status,
            'note'       => 'Changed from "' . $booking->getOriginal('status') . '" to "' . $booking->status . '"',
        ]);

        // Cancellation — أوتوماتيك لما status يبقى cancelled
        if ($booking->status === 'cancelled') {
            \App\Models\Cancellation::create([
                'booking_id'   => $booking->id,
                'reason'       => null,
                'cancelled_at' => now(),
            ]);
        }
    }
}

public function created(Booking $booking)
{
    app(\App\Services\CustomerTagService::class)
        ->updateTag($booking->customer_id);
}

public function deleted(Booking $booking)
{
    app(\App\Services\CustomerTagService::class)
        ->updateTag($booking->customer_id);
}
}