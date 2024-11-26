<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['nom', 'province_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
}; 