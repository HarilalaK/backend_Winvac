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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('im')->unique()->nullable();
            $table->string('cin', 12);
            $table->string('nom_prenom');
            $table->date('date_cin');
            $table->string('lieu_cin');
            $table->string('attribution');
            $table->char('sexe', 1);
            $table->date('date_entree');
            $table->enum('statut', ['Admin', 'DR', 'Operateur'])->default('Operateur');
            $table->integer('ref_st')->default(0);
            $table->string('contact', 10);
            $table->string('password');
            $table->string('photo')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
