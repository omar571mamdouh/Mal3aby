<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'branch_id',
        'name',
        'phone',
        'email',
        'specialty',
        'bio',
        'hire_date',
        'commission_type',
        'commission_value',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'commission_value' => 'decimal:2',
    ];

    /* ================== Relationships ================== */

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function schedules()
    {
        return $this->hasMany(CoachSchedule::class);
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function commissions()
    {
        return $this->hasMany(CoachCommission::class);
    }
}