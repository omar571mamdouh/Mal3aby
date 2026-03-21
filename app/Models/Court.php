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

    public function timeSlots()
{
    return $this->hasMany(CourtTimeSlot::class);
}

public function blackoutDates()
{
    return $this->hasMany(BlackoutDate::class);
}

public function seasonalPricing()
{
    return $this->hasMany(SeasonalPricing::class);
}

public function dynamicPricingRules()
{
    return $this->hasMany(DynamicPricingRule::class);
}

public function maintenanceLogs()
{
    return $this->hasMany(MaintenanceLog::class);
}
}
