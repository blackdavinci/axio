<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum statut pour ajouter les nouveaux statuts
        DB::statement("ALTER TABLE courriers MODIFY COLUMN statut ENUM('recu', 'en_attente_assignation', 'affecte', 'en_cours_traitement', 'traite', 'archive', 'rejete') DEFAULT 'recu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien enum
        DB::statement("ALTER TABLE courriers MODIFY COLUMN statut ENUM('recu', 'en_cours', 'traite', 'archive', 'rejete') DEFAULT 'recu'");
    }
};
