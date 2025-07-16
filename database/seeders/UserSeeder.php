<?php

namespace Database\Seeders;

use App\Enum\RolesEnum;
use App\Models\Permission;
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
        // Criando usuário Admin
        $admin = User::firstOrCreate([
            'name' => 'Admin',
            'email' => env('EMAIL_USER_ADMIN'),
            'password' => bcrypt(env('PASSWORD_USER_ADMIN')),
        ]);


        $adminRole = Role::where('name', 'ADMIN')->first();

        $admin->assignRole($adminRole);
    }
}
