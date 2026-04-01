<?php

namespace App\Services;

use App\Models\SeasonalPricing;
use App\Models\DynamicPricingRule;

class PricingService
{
   public function calculate($court, $slot, $booking_date)
{
    // 1. Base price
    $basePrice = $slot->price ?? $court->price ?? 0;

    // 2. Seasonal pricing
    $seasonal = SeasonalPricing::where('court_id', $court->id)
        ->where('start_date', '<=', $booking_date)
        ->where(function ($q) use ($booking_date) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $booking_date);
        })
        ->first();

    $price = $seasonal ? $seasonal->price : $basePrice;

    // 👇 مهم
    $originalPrice = $price;

    // 3. Dynamic rules
    $rules = DynamicPricingRule::where('court_id', $court->id)->get();

    foreach ($rules as $rule) {

        if ($rule->type === 'percentage') {
            // ✅ نسبة مئوية (20 = 20%)
            $price -= $originalPrice * ($rule->modifier / 100);
        } else {
            // ✅ خصم ثابت
            $price -= $rule->modifier;
        }
    }

    // 👇 عشان السعر ميبقاش بالسالب
    return max(round($price, 2), 0);
}
}