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
            $table->foreignId('service_id')->nullable()->after('email_verified_at')->constrained('services')->onDelete('set null');
            $table->string('telephone')->nullable()->after('service_id');
            $table->string('poste')->nullable()->after('telephone');
            $table->boolean('actif')->default(true)->after('poste');
            
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'telephone', 'poste', 'actif']);
        });
    }
};
