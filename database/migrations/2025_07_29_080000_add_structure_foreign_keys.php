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
        // Ajouter les clés étrangères après création de toutes les tables
        Schema::table('structures', function (Blueprint $table) {
            if (Schema::hasColumn('structures', 'chef_id') && !Schema::hasTable('structures_chef_id_foreign')) {
                $table->foreign('chef_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropForeign(['chef_id']);
        });
    }
};