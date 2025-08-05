<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // CRÉER SEULEMENT L'UTILISATEUR
        $superAdmin = User::create([
            'nom' => 'Morgan',
            'prenom' => 'Alfred',
            'name' => 'alfredmorgan',
            'genre' => 'M',
            'email' => 'admin@axio.gov.gn',
            'password' => bcrypt('supersecure'), // 🔐 À personnaliser en production
            'email_verified_at' => now(),
            'statut' => true
        ]);

        // ASSIGNER LE RÔLE
        $role = Role::where('name', 'super_admin')->first();


        $superAdmin->assignRole($role);

    }
}
