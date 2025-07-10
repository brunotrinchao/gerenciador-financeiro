<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
//            ['name' => 'Banco do Brasil',        'code' => 1],
            ['name' => 'Banco da Amazônia',      'code' => 3],
            ['name' => 'Banco do Nordeste',      'code' => 4],
//            ['name' => 'Caixa Econômica Federal','code' => 104],
//            ['name' => 'Bradesco',               'code' => 237],
            ['name' => 'Santander',              'code' => 33],
            ['name' => 'Itaú Unibanco',          'code' => 341],
            ['name' => 'Banco Safra',            'code' => 422],
            ['name' => 'Banco Inter',            'code' => 77],
            ['name' => 'Nubank',                 'code' => 260],
            ['name' => 'Banco Pan',              'code' => 623],
            ['name' => 'Banco Original',         'code' => 212],
            ['name' => 'C6 Bank',                'code' => 336],
            ['name' => 'BTG Pactual',            'code' => 208],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}
