<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlackoutDate extends Model
{
    use HasFactory;

    // الأعمدة اللي مسموح mass assignment عليها
    protected $fillable = [
        'court_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'type',
        'reason',
        'active',
    ];

    // تحويل الأعمدة للتعامل مع التواريخ والأوقات مباشرة
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'active' => 'boolean',
    ];

    /**
     * العلاقة مع الـ Court
     */
    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Scope لفلترة الـ active فقط
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope للتحقق من وجود blackout بتاريخ معين ووقت محدد
     */
    public function scopeConflicting($query, $courtId, $date, $startTime = null, $endTime = null)
    {
        return $query->where('court_id', $courtId)
            ->where('active', true)
            ->where(function ($q) use ($date) {
                $q->whereDate('start_date', '<=', $date)
                  ->where(function ($q2) use ($date) {
                      $q2->whereNull('end_date')
                         ->orWhereDate('end_date', '>=', $date);
                  });
            })
            ->when($startTime && $endTime, function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->whereNull('start_time') // يوم كامل
                       ->orWhere(function ($q3) use ($startTime, $endTime) {
                           $q3->whereTime('start_time', '<', $endTime)
                              ->whereTime('end_time', '>', $startTime);
                       });
                });
            });
    }
}