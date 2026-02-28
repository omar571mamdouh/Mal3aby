<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'club_id',
        'name',
        'address',
        'city',
        'phone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}