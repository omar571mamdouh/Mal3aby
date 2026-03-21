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
        Schema::create('seasonal_pricing', function (Blueprint $table) {

    $table->id();

    $table->foreignId('court_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->date('start_date');
    $table->date('end_date');

    $table->decimal('price',8,2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_pricing');
    }
};
