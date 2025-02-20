<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            
            // Champs pour les rôles fixes (PDO, VPDO, CDC, CDCA)
            $table->decimal('taux_forfaitaire', 10, 2)->nullable();
            
            // Champs communs pour tous les types d'agents
            $table->integer('jours_travaille')->nullable();
            $table->decimal('taux_journalier', 10, 2)->nullable();
            
            // Champs spécifiques pour les surveillants
            $table->integer('jours_surveillance')->nullable();
            $table->integer('jours_encours')->nullable();
            $table->integer('jours_ensalles')->nullable();
            $table->decimal('taux_par_jour', 10, 2)->nullable();
            
            // Champs spécifiques pour les correcteurs
            $table->string('matiere')->nullable();
            $table->integer('nombre_copie')->nullable();
            $table->decimal('taux_par_copie', 10, 2)->nullable();
            
            // Champs de calcul communs
            $table->decimal('taux_brut', 10, 2);
            $table->decimal('irsa', 10, 2);
            $table->decimal('taux_net', 10, 2);
            
            // Champs d'audit
            $table->string('created_by')->default('HarilalaK');
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_details');
    }
};