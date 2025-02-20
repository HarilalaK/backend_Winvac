<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taux_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->decimal('taux_forfaitaire', 10, 2)->nullable(); // Pour PDO, VPDO, CDC, CDCA
            $table->decimal('taux_journalier', 10, 2)->nullable();  // Pour secretaire, secOrg, surveillance, securite
            $table->decimal('taux_base_correcteur', 10, 2)->nullable(); // Taux de base pour <= 100 copies
            $table->decimal('taux_surplus_bep', 10, 2)->nullable(); // Taux surplus BEP
            $table->decimal('taux_surplus_autres', 10, 2)->nullable(); // Taux surplus autres
            $table->string('created_by')->default('HarilalaK');
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taux_roles');
    }
}; 