<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'points',
        'transaction_type', // earned / redeemed
        'description',
        'transaction_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}