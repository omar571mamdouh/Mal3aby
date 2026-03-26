<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTag extends Model
{
    protected $fillable = ['customer_id', 'tag']; // ← مش name/color
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}