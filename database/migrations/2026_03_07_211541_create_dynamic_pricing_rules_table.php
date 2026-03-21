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
       Schema::create('dynamic_pricing_rules', function (Blueprint $table) {

    $table->id();

    $table->foreignId('court_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('rule_name');

    $table->decimal('modifier',5,2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_pricing_rules');
    }
};
