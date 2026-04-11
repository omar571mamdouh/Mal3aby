<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipFreeHour extends Model
{
    protected $fillable = [
        'customer_membership_id', 'total_hours', 'used_hours'
    ];

    public function customerMembership()
    {
        return $this->belongsTo(CustomerMembership::class);
    }

    public function getRemainingHoursAttribute()
    {
        return $this->total_hours - $this->used_hours;
    }
}
