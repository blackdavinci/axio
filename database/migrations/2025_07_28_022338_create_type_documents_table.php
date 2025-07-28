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
        Schema::create('type_documents', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->string('couleur', 7)->default('#8B5CF6'); // Couleur en hexadecimal
            $table->string('icone')->default('heroicon-o-document'); // Icône Heroicon
            $table->json('extensions_autorisees')->nullable(); // ['pdf', 'doc', 'docx']
            $table->boolean('actif')->default(true);
            $table->integer('ordre_affichage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_documents');
    }
};
