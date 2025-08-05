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
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique();
            $table->enum('type', ['departement', 'service']);
            $table->text('description')->nullable();
            
            // Hiérarchie flexible
            $table->foreignId('parent_id')->nullable()->constrained('structures');
            
            // Chef/responsable (sera ajouté après création de la table users)
            $table->unsignedBigInteger('chef_id')->nullable();
            
            // Gestion
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0);
            
            $table->timestamps();
            
            // Index pour optimisation
            $table->index(['type', 'parent_id']);
            $table->index(['actif', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
