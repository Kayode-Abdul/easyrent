<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            [
                'code' => 'NGN',
                'symbol' => '₦',
                'name' => 'Nigerian Naira',
                'is_active' => true,
            ],
            [
                'code' => 'USD',
                'symbol' => '$',
                'name' => 'US Dollar',
                'is_active' => true,
            ],
            [
                'code' => 'GBP',
                'symbol' => '£',
                'name' => 'British Pound',
                'is_active' => true,
            ],
            [
                'code' => 'EUR',
                'symbol' => '€',
                'name' => 'Euro',
                'is_active' => true,
            ],
            [
                'code' => 'GHS',
                'symbol' => 'GH₵',
                'name' => 'Ghanaian Cedi',
                'is_active' => true,
            ],
            [
                'code' => 'KES',
                'symbol' => 'KSh',
                'name' => 'Kenyan Shilling',
                'is_active' => true,
            ],
            [
                'code' => 'ZAR',
                'symbol' => 'R',
                'name' => 'South African Rand',
                'is_active' => true,
            ],
            [
                'code' => 'EGP',
                'symbol' => 'E£',
                'name' => 'Egyptian Pound',
                'is_active' => true,
            ],
            [
                'code' => 'MAD',
                'symbol' => 'MAD',
                'name' => 'Moroccan Dirham',
                'is_active' => true,
            ],
            [
                'code' => 'XAF',
                'symbol' => 'FCFA',
                'name' => 'Central African CFA Franc',
                'is_active' => true,
            ],
            [
                'code' => 'XOF',
                'symbol' => 'CFA',
                'name' => 'West African CFA Franc',
                'is_active' => true,
            ],
            [
                'code' => 'RWF',
                'symbol' => 'RF',
                'name' => 'Rwandan Franc',
                'is_active' => true,
            ],
            [
                'code' => 'UGX',
                'symbol' => 'USh',
                'name' => 'Ugandan Shilling',
                'is_active' => true,
            ],
            [
                'code' => 'ETB',
                'symbol' => 'Br',
                'name' => 'Ethiopian Birr',
                'is_active' => true,
            ],
            [
                'code' => 'TZS',
                'symbol' => 'TSh',
                'name' => 'Tanzanian Shilling',
                'is_active' => true,
            ],
            [
                'code' => 'ZMW',
                'symbol' => 'ZK',
                'name' => 'Zambian Kwacha',
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
