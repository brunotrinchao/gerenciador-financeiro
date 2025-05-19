<?php

namespace Database\Seeders;

use App\Models\BrandCard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'visa' => 'Visa',
            'mastercard' => 'Mastercard',
            'elo' => 'Elo',
            'amex' => 'American Express',
            'hipercard' => 'Hipercard',
            'diners' => 'Diners Club',
            'discover' => 'Discover',
        ];

        foreach ($brands as $key => $value) {
            BrandCard::create([
                'name' => $value,
                'slug' => $key,
            ]);
        }
    }
}
