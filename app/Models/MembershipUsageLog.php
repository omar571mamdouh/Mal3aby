<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipUsageLog extends Model
{
    protected $fillable = [
        'customer_membership_id',
        'booking_id',
        'used_hours',
        'discount_amount'
    ];

    public function customerMembership()
    {
        return $this->belongsTo(CustomerMembership::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}