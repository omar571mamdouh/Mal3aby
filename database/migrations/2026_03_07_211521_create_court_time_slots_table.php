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
       Schema::create('court_time_slots', function (Blueprint $table) {

    $table->id();

    $table->foreignId('court_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->time('start_time');
    $table->time('end_time');

    $table->decimal('price',8,2)->nullable();

    $table->boolean('active')->default(true);

    $table->timestamps();

    $table->unique(['court_id','start_time','end_time']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_time_slots');
    }
};
