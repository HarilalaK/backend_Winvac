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
        Schema::create('matieres', function (Blueprint $table) {
            $table->id();
            $table->integer('num_matiere');
            $table->string('code')->unique();
            $table->string('designation');
            $table->boolean('BEP')->default(false);
            $table->boolean('CAP')->default(false);
            $table->boolean('CFA')->default(false);
            $table->boolean('ConcoursLTP')->default(false);
            $table->boolean('ConcoursCFP')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};
