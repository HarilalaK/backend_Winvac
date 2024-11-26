<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['province', 'region', 'centre']);
            $table->foreignId('centre_id')->after('annee')->constrained('centres');
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropColumn('centre_id');
            $table->string('province');
            $table->string('region');
            $table->string('centre');
        });
    }
}; 