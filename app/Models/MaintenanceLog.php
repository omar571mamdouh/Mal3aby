<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'court_id',
        'maintenance_date',
        'note'
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}