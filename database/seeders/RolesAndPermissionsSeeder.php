<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'manage own billing',
            'download own certificates',
            'register for trainings',
            'submit clinicals',
            'request discount',
            'create trainings',
            'edit trainings',
            'mark training attendees complete',
            'view training attendee lists',
            'view payout reports',
            'connect stripe account',
            'access filament admin',
            'approve deny discount requests',
            'manage all users',
            'configure payout percentages',
            'issue revoke certificates',
            'manage plans prices',
            'manage own products',
            'view own orders',
            'update order status',
            'view vendor payout reports',
            'manage vendor profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $member = Role::firstOrCreate(['name' => 'member']);
        $member->syncPermissions([
            'view dashboard',
            'manage own billing',
            'download own certificates',
            'register for trainings',
            'submit clinicals',
            'request discount',
        ]);

        $trainer = Role::firstOrCreate(['name' => 'registered_trainer']);
        $trainer->syncPermissions([
            'view dashboard',
            'manage own billing',
            'download own certificates',
            'register for trainings',
            'submit clinicals',
            'request discount',
            'create trainings',
            'edit trainings',
            'mark training attendees complete',
            'view training attendee lists',
            'view payout reports',
            'connect stripe account',
        ]);

        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $vendor->syncPermissions([
            'view dashboard',
            'manage own billing',
            'manage own products',
            'view own orders',
            'update order status',
            'view vendor payout reports',
            'manage vendor profile',
            'connect stripe account',
        ]);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view dashboard',
            'view own orders',
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }
}
