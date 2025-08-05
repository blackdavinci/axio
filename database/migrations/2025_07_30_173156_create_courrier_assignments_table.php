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
        Schema::create('courrier_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')->constrained('courriers')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete(); // Structure assignée
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Agent spécifique assigné
            $table->text('notes')->nullable(); // Notes liées à cette assignation
            $table->timestamp('assigned_at')->useCurrent(); // Date de l'assignation
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete(); // Qui a assigné
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courrier_assignments');
    }
};
