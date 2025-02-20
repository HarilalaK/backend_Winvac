<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centres', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->integer('nombre_salles')->nullable();
            $table->integer('nombre_candidats')->nullable();
            $table->string('numero_centre')->nullable();
            $table->enum('type_examen', ['BEP', 'CFA', 'CAP', 'ConcoursLTP', 'ConcoursCFP'])->nullable();
            $table->year('session')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['nom', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centres');
    }
};