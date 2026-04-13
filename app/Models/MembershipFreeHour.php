<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipFreeHour extends Model
{
    protected $fillable = [
        'customer_membership_id',
        'total_hours',
        'used_hours'
    ];
    
    protected $casts = [
        'total_hours' => 'float',
        'used_hours'  => 'float',
    ];

    // في MembershipFreeHour Model
    protected $appends = ['remaining_hours'];

    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->total_hours - $this->used_hours);
    }

    public function customerMembership()
    {
        return $this->belongsTo(CustomerMembership::class);
    }
}
