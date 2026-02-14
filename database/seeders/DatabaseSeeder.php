<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            GlobalPayoutSettingSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'first_name' => 'NADA',
            'last_name' => 'Admin',
            'email' => 'admin@acudetox.com',
        ]);
        $admin->assignRole('admin');
    }
}
