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
        Schema::create('expediteurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('type', ['personne', 'entreprise', 'administration','autre'])->default('administration');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->timestamps();

            // Index pour optimisation des recherches
            $table->index(['nom', 'type']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expediteurs');
    }
};
