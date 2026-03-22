<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blackout_dates', function (Blueprint $table) {

            $table->id();

            $table->foreignId('court_id')
                ->constrained()
                ->cascadeOnDelete();

            // بدل date واحدة
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // دعم partial time
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // نوع البلوك
            $table->enum('type', ['maintenance', 'holiday', 'event', 'manual'])
                  ->default('manual');

            $table->string('reason')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            // index مهم للأداء عند check الحجز
            $table->index(['court_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blackout_dates');
    }
};