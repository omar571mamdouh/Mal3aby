<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_features', function (Blueprint $table) {
            $table->unique(['membership_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('membership_features', function (Blueprint $table) {
            $table->dropUnique(['membership_features_membership_id_type_unique']);
        });
    }
};
