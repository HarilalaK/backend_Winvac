<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->decimal('prix', 10, 2);
            $table->boolean('requiert_jours_travaille')->default(false);
            $table->boolean('requiert_jours_surveillance')->default(false);
            $table->boolean('requiert_copies')->default(false);
            $table->boolean('requiert_matiere')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
}; 