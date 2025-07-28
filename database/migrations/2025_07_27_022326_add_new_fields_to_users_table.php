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
        Schema::table('users', function (Blueprint $table) {
            // Retirer le champ 'name' existant - sera remplacé par prenom et nom
            $table->dropColumn('name');
            
            // Champs priorité haute
            $table->string('prenom')->after('id');
            $table->string('nom')->after('prenom');
            $table->enum('genre', ['M', 'F'])->after('nom');
            $table->string('photo')->nullable()->after('genre');
            $table->string('telephone_secondaire')->nullable()->after('telephone');
            $table->string('matricule')->unique()->nullable()->after('telephone_secondaire');
            $table->string('grade')->nullable()->after('matricule');
            $table->text('adresse')->nullable()->after('grade');
            
            // Champs priorité moyenne sélectionnés
            $table->date('date_naissance')->nullable()->after('adresse');
            $table->enum('categorie', ['fonctionnaire', 'contractuel', 'consultant', 'stagiaire'])->nullable()->after('date_naissance');
            $table->string('specialite')->nullable()->after('categorie');
            $table->string('personne_urgence')->nullable()->after('specialite');
            $table->string('telephone_urgence')->nullable()->after('personne_urgence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remettre le champ 'name'
            $table->string('name')->after('id');
            
            // Supprimer les nouveaux champs
            $table->dropColumn([
                'prenom',
                'nom', 
                'genre',
                'photo',
                'telephone_secondaire',
                'matricule',
                'grade',
                'adresse',
                'date_naissance',
                'categorie',
                'specialite',
                'personne_urgence',
                'telephone_urgence'
            ]);
        });
    }
};
