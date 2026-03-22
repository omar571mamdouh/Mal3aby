<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalPricing extends Model
{
    protected $table = 'seasonal_pricing'; // نفس اسم الجدول بالضبط

    // الحقول اللي ممكن تعمل لها fill
    protected $fillable = [
        'court_id',
        'start_date',
        'end_date',
        'price',
        'season', // لو عايز تضيف حقل لتحديد الصيف/الشتا مثلاً
    ];
     
   protected $casts = [
    'start_date' => 'date',
    'end_date'   => 'date',
];

    // العلاقة مع الملعب
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    // لو تحب تحط Accessor عشان تعرض الفترة بشكل مرتب
    public function getPeriodAttribute(): string
    {
        $start = $this->start_date->format('Y-m-d');
        $end = $this->end_date ? $this->end_date->format('Y-m-d') : '—';
        return $start . ($end !== '—' ? " → $end" : '');
    }
}