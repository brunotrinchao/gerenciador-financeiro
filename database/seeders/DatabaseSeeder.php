<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
//            RoleSeeder::class,
//            UserSeeder::class,
            PermissionSeeder::class,
            CategorySeed::class,
            BrandCardSeeder::class,
            BankSeeder::class,
            AccountSeeder::class,
            CardSeeder::class,
            TransactionSeeder::class,
            TransactionItemSeeder::class
        ]);
    }
}
