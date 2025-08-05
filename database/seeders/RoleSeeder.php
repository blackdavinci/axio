<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin Role and assign all permissions
        Role::create(['name' => 'super_admin']);

        Role::create(['name' => 'directeur']);

        Role::create(['name' => 'secretaire']);

        Role::create(['name' => 'agent']);

        Role::create(['name' => 'chef']);

        Role::create(['name' => 'partenaire']);

        Role::create(['name' => 'public']);
    }
}
