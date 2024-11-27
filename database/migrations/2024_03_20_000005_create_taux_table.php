<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taux', function (Blueprint $table) {
            $table->id();
            $table->year('annee');
            $table->decimal('secretariat', 10, 2);
            $table->decimal('surveillance_securite', 10, 2);
            $table->decimal('correction_max_copies', 10, 2);
            $table->decimal('correction_surplus_bep', 10, 2);
            $table->decimal('correction_surplus_autre', 10, 2);
            $table->decimal('forfaitaire_pdo_vpdo', 10, 2);
            $table->decimal('forfaitaire_cdc_cdca', 10, 2);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('annee');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('taux');
    }
}; 