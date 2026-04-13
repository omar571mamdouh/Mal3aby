<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CustomerMembership;
use App\Models\MembershipUsageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function applyToBooking(Booking $booking): array
    {
        if (!$booking->customer_id) {
            return ['used_hours' => 0, 'discount' => 0];
        }

        $bookingDate = Carbon::parse($booking->booking_date);

        $customerMembership = CustomerMembership::with([
                'membership.features',
                'freeHours'
            ])
            ->where('customer_id', $booking->customer_id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $bookingDate)
            ->whereDate('end_date', '>=', $bookingDate)
            ->latest()
            ->first();

        if (!$customerMembership) {
            return ['used_hours' => 0, 'discount' => 0];
        }

        $usedHours = 0;
        $discountAmount = 0;

        DB::transaction(function () use (
            $booking,
            $customerMembership,
            &$usedHours,
            &$discountAmount
        ) {

            $freeHour = $customerMembership->freeHours;

            if ($freeHour) {

                $remaining = $freeHour->total_hours - $freeHour->used_hours;

                if ($remaining > 0) {

                    $bookingHours = $booking->durationHours();

                    $usedHours = min($bookingHours, $remaining);

                    $newUsed = $freeHour->used_hours + $usedHours;

                    $freeHour->update([
                        'used_hours' => min($newUsed, $freeHour->total_hours)
                    ]);
                }
            }

            $discountFeature = $customerMembership
                ->membership
                ->features
                ->where('type', 'discount')
                ->first();

            if ($discountFeature) {

                $discountAmount = round(
                    $booking->price * ($discountFeature->value / 100),
                    2
                );

                $booking->update([
                    'price' => max(0, $booking->price - $discountAmount),
                ]);
            }

            if ($usedHours > 0 || $discountAmount > 0) {
                MembershipUsageLog::create([
                    'customer_membership_id' => $customerMembership->id,
                    'booking_id' => $booking->id,
                    'used_hours' => $usedHours,
                    'discount_amount' => $discountAmount,
                ]);
            }
        });

        return [
            'used_hours' => $usedHours,
            'discount' => $discountAmount,
        ];
    }
}