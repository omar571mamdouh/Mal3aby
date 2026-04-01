<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CustomerTag;

class CustomerTagService
{
    public function updateTag(int $customerId)
    {
        $bookingsCount = Booking::where('customer_id', $customerId)->count();

        // 🎯 تحديد التاج حسب كل 10 bookings
        if ($bookingsCount < 10) {
            $tag = 'new';
        } elseif ($bookingsCount < 20) {
            $tag = 'bronze';
        } elseif ($bookingsCount < 30) {
            $tag = 'silver';
        } elseif ($bookingsCount < 40) {
            $tag = 'gold';
        } elseif ($bookingsCount < 50) {
            $tag = 'plat';
        } elseif ($bookingsCount < 60) {
            $tag = 'diamond';
        } else {
            $tag = 'vip';
        }

        // 🧠 تحديث أو إنشاء التاج
        CustomerTag::updateOrCreate(
            ['customer_id' => $customerId],
            ['tag' => $tag]
        );
    }
}