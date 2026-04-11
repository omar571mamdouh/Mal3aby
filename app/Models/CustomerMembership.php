<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMembership extends Model
{
    protected $fillable = [
        'customer_id', 'membership_id', 'start_date', 'end_date', 'status'
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function freeHours()
    {
        return $this->hasOne(MembershipFreeHour::class);
    }

    public function usageLogs()
    {
        return $this->hasMany(MembershipUsageLog::class);
    }

    // 🔥 Helper
    public function isActive()
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }
}