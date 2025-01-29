<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('securite_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->integer('jours_travaille');
            $table->decimal('taux_journalier', 10, 2);
            $table->decimal('taux_brut', 10, 2);
            $table->decimal('irsa', 10, 2);
            $table->decimal('taux_net', 10, 2);
            $table->string('created_by')->default('HarilalaK');
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('securite_details');
    }
};