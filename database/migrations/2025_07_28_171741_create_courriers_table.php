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
        Schema::create('courriers', function (Blueprint $table) {
            $table->id();
            $table->string('numero_courrier')->unique();
            $table->string('reference')->nullable();
            $table->string('objet');
            $table->enum('statut', ['recu', 'en_cours', 'traite', 'archive', 'rejete'])->default('recu');
            $table->datetime('date_limite_traitement')->nullable();
            $table->datetime('date_reception')->default(now());

            // Relations
            $table->foreignId('type_courrier_id')->constrained('type_courriers');
            $table->foreignId('priorite_id')->nullable()->constrained('priorites');
            $table->foreignId('expediteur_id')->constrained('expediteurs');
            $table->foreignId('created_by')->constrained('users'); // Créé par

            // Champs de suivi
            $table->text('commentaires')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courriers');
    }
};
