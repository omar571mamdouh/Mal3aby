<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'note',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}