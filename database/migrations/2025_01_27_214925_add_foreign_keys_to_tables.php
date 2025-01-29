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

        // Ajouter la clé étrangère dans pdo_details
        Schema::table('pdo_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans vpdo_details
        Schema::table('vpdo_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans cdc_details
        Schema::table('cdc_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans cdca_details
        Schema::table('cdca_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans secretaire_details
        Schema::table('secretaire_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans sec_org_details
        Schema::table('sec_org_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans surveillance_details
        Schema::table('surveillance_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans securite_details
        Schema::table('securite_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });

        // Ajouter la clé étrangère dans correcteur_details
        Schema::table('correcteur_details', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Supprimer toutes les clés étrangères dans l'ordre inverse
        Schema::table('correcteur_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('securite_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('surveillance_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('sec_org_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('secretaire_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('cdca_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('cdc_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('vpdo_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('pdo_details', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });
    }
};