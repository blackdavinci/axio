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
        Schema::create('priorites', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->string('couleur', 7)->default('#3B82F6'); // Couleur en hexadecimal
            $table->string('icone')->default('heroicon-o-flag'); // Icône Heroicon
            $table->integer('delai_defaut')->default(7); // Délai en jours
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
        Schema::dropIfExists('priorites');
    }
};
