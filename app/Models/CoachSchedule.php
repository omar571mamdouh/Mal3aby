<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoachSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'branch_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /* ================== Relationships ================== */

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /* ================== Helpers ================== */

    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }
}