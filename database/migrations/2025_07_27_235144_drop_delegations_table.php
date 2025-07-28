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
        Schema::dropIfExists('delegations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table si nécessaire (structure de base)
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('delegataire_id')->constrained('users')->onDelete('cascade');
            $table->string('perimetre');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('actif')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};
