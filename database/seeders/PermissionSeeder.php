<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'transactions' => ['view', 'create', 'edit', 'delete'],
            'transaction_items' => ['view', 'create', 'edit', 'delete'],
            'accounts' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'dashboard' => ['access'],
        ];

        // Cria todas as permissões
        foreach ($permissions as $group => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$group}"]);
            }
        }

        $allPermissions = Permission::all();

        // ADMIN: todas as permissões
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $adminRole->syncPermissions($allPermissions);

        // USER: permissões básicas
        $userPermissions = Permission::whereIn('name', [
            'view transactions',
            'create transactions',
            'edit transactions',
            'view transaction_items',
            'create transaction_items',
            'edit transaction_items',
            'view accounts',
            'access dashboard',
        ])->get();

        $userRole = Role::firstOrCreate(['name' => 'USER']);
        $userRole->syncPermissions($userPermissions);

        // GUEST: somente visualização
        $guestPermissions = Permission::whereIn('name', [
            'view transactions',
            'view transaction_items',
            'view accounts',
            'access dashboard',
        ])->get();

        $guestRole = Role::firstOrCreate(['name' => 'GUEST']);
        $guestRole->syncPermissions($guestPermissions);

        // Cria usuário admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => bcrypt('admin')]
        );
        $admin->assignRole($adminRole);
    }

}
