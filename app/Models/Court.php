<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $fillable = ['facility_id','name','surface_type','price_per_hour','opens_at','closes_at','capacity','active'];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
