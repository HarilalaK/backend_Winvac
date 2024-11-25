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
            $table->string('province');
            $table->string('region');
            $table->string('centre');
            $table->string('situation');
            $table->string('role');
            $table->integer('jours_travaille');
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
