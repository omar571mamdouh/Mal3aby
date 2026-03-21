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
    Schema::table('court_time_slots', function (Blueprint $table) {
        $table->enum('day', [
            'sunday','monday','tuesday',
            'wednesday','thursday','friday','saturday'
        ])->after('court_id')->nullable();
    });
}

public function down(): void
{
    Schema::table('court_time_slots', function (Blueprint $table) {
        $table->dropColumn('day');
    });
}
};
