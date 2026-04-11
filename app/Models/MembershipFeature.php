<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipFeature extends Model
{
    protected $fillable = [
        'membership_id', 'type', 'value'
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }
}