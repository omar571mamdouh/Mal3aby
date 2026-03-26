<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicPricingRule extends Model
{
    protected $fillable = [
        'court_id',
        'rule_name',
        'modifier',
        'type',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}