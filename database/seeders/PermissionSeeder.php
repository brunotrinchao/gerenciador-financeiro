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

        foreach ($permissions as $group => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$group}"]);
            }
        }

        // Cria a role ADMIN e atribui todas as permissões
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $adminRole->syncPermissions(Permission::all());

        // Cria um usuário admin se não existir
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => bcrypt('admin')]
        );


        $admin->assignRole($adminRole);
    }
}
