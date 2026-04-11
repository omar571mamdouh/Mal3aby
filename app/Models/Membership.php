<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration_type',
        'duration_value',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function features()
    {
        return $this->hasMany(MembershipFeature::class);
    }

    public function customerMemberships()
    {
        return $this->hasMany(CustomerMembership::class);
    }
}
