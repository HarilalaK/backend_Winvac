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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_op');  // Correspond à Date_Op
            $table->string('operateur');  // Correspond à Operateur
            $table->string('operations'); // Correspond à Operations
            $table->string('cin');        // Correspond à CIN
            $table->string('nom_prenom'); // Correspond à Nom_Prenom
            $table->text('autres')->nullable(); // Correspond à Autres
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
