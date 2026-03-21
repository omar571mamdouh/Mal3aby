<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtTimeSlot extends Model
{
    protected $fillable = [
        'court_id',
        'start_time',
        'day',
        'end_time',
        'price',
        'active'
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}