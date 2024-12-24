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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->year('annee');
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');
            $table->string('situation');
            $table->enum('role', ['PDO', 'VPDO', 'CDC', 'CDCA', 'secretaire', 'secOrg', 'surveillance', 'securite', 'correcteur']);
            $table->enum('typeExamen', ['BEP', 'CFA', 'CAP', 'ConcoursLTP', 'ConcoursCFP']);
            $table->integer('jours_travaille')->nullable();
            $table->string('im')->nullable();
            $table->string('cin');
            $table->string('nom');
            $table->string('prenom');
            $table->enum('sexe', ['M', 'F']);
            $table->string('lieu_cin');
            $table->date('date_cin');
            $table->string('matiere')->nullable();
            $table->integer('nombre_copie')->nullable();
            $table->integer('jours_surveillance')->nullable();
            $table->integer('jours_encours')->nullable();
            $table->integer('jours_ensalles')->nullable();
            $table->decimal('taux_brut', 10, 2)->nullable();
            $table->decimal('irsa', 10, 2)->nullable();
            $table->decimal('taux_net', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
