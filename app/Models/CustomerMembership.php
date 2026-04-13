<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMembership extends Model
{
    protected $fillable = [
        'customer_id', 'membership_id', 'start_date', 'end_date', 'status'
    ];

    // ✅ ضيف الـ casts دي
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

  public function isActive(): bool
{
    return $this->status === 'active'
        && now()->startOfDay()->lte($this->end_date->endOfDay());
}
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
}