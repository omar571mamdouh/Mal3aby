<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'club_id',
        'branch_id',
        'facility_id',
        'status',
    ];

    // علاقات
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function tags()
    {
        return $this->belongsToMany(CustomerTag::class, 'customer_tags', 'customer_id', 'tag_id');
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
    // App\Models\Customer.php
public function bookings()
{
    return $this->hasMany(Booking::class);
}
}