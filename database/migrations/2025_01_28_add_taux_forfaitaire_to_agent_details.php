<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_details', function (Blueprint $table) {
            // Ajout du champ taux_forfaitaire après agent_id
            $table->decimal('taux_forfaitaire', 10, 2)->nullable()->after('agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('agent_details', function (Blueprint $table) {
            $table->dropColumn('taux_forfaitaire');
        });
    }
}; 