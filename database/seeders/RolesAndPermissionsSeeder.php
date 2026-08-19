<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions per model
        $models = [
            'employees',
            'customers',
            'roles',
            'permissions',
            'services',
            'sub_services',
            'subservience_specifications',
            'faqs',
            'items',
            'parts',
            'service_types',
        ];

        $permissions = [];
        foreach ($models as $model) {
            $permissions[] = Permission::firstOrCreate(['name' => "view $model"]);
            $permissions[] = Permission::firstOrCreate(['name' => "create $model"]);
            $permissions[] = Permission::firstOrCreate(['name' => "update $model"]);
            $permissions[] = Permission::firstOrCreate(['name' => "delete $model"]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $teamLeader = Role::firstOrCreate(['name' => 'team_leader']);
        $customerService = Role::firstOrCreate(['name' => 'customer_service']);
        // Assign all permissions to super_admin
        $superAdmin->syncPermissions(Permission::all());
        // Assign partial permissions to other roles (example)
        $admin->syncPermissions(Permission::whereIn('name', [
            'view appointments',
            'create appointments',
            'update appointments',
            'view items',
            'view parts',
        ])->get());

        $teamLeader->syncPermissions(Permission::whereIn('name', [
            'view tasks',
            'update tasks',
            'create tasks',
        ])->get());

        $customerService->syncPermissions(Permission::whereIn('name', [
            'view appointments',
            'create appointments',
        ])->get());

        // Create example users and assign roles with permissions
        $users = [
            ['username' => 'superadmin', 'email' => 'super@admin.com', 'role' => 'super_admin'],
            ['username' => 'adminuser', 'email' => 'admin@admin.com', 'role' => 'admin'],
            ['username' => 'teamlead', 'email' => 'lead@team.com', 'role' => 'team_leader'],
            ['username' => 'support', 'email' => 'support@service.com', 'role' => 'customer_service'],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'username' => $userData['username'],
                    'password' => bcrypt('password'),
                    'role' => $userData['role'],
                    'type' => 'employee',
                ]
            );

            // Assign role to the user
            $user->assignRole($userData['role']);
            // Sync the permissions associated with the role
            $role = Role::findByName($userData['role']);
            $user->syncPermissions($role->permissions);
        }

    }
}
