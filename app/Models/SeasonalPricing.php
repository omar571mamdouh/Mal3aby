<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalPricing extends Model
{
    protected $fillable = [
        'court_id',
        'start_date',
        'end_date',
        'price'
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}