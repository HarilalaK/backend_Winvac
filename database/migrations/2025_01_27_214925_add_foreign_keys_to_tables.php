<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la clé étrangère dans la table agents
        Schema::table('agents', function (Blueprint $table) {
            $table->foreign('centre_id')->references('id')->on('centres')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans agent_details
        Schema::table('agent_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Supprimer les clés étrangères dans l'ordre inverse
        Schema::table('agent_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });
    }
};