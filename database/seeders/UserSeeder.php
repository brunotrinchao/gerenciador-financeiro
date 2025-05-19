<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['type' => 'ADMIN']);
        $userRole = Role::firstOrCreate(['type' => 'ADMIN']);

        // Criando usuário Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'role_id' => $adminRole->id,
        ]);

        // Criando usuário comum
        User::create([
            'name' => 'User Example',
            'email' => 'user@user.com',
            'password' => Hash::make('admin'),
            'role_id' => $userRole->id,
        ]);
    }
}
