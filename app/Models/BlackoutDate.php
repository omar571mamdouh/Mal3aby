<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackoutDate extends Model
{
    protected $fillable = [
        'court_id',
        'date',
        'reason'
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}