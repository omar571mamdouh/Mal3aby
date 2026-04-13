<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0 = Sunday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // لمنع تكرار نفس الجدول للمدرب في نفس اليوم والوقت
            $table->unique([
                'coach_id',
                'branch_id',
                'day_of_week',
                'start_time',
                'end_time'
            ], 'coach_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_schedules');
    }
};
