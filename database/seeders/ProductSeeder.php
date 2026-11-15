<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'name' => 'Survêtement style militaire kaki',
                'description' => 'Ensemble veste + pantalon, coupe treillis, coloris kaki.',
                'price' => 40000,
                'image' => 'survetement_kaki.jpg',
                'stock' => 20,
                'category' => 'survetement',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Veste tactique noire',
                'description' => 'Veste renforcée, multi-poches, coupe ajustée.',
                'price' => 49000,
                'image' => 'veste_tactique_noire.jpg',
                'stock' => 15,
                'category' => 'veste',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pantalon cargo camouflage',
                'description' => 'Pantalon cargo motif camouflage, poches renforcées.',
                'price' => 30000,
                'image' => 'pantalon_camo.jpg',
                'stock' => 25,
                'category' => 'pantalon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Casquette style commando',
                'description' => 'Casquette réglable, coloris kaki.',
                'price' => 20000,
                'image' => 'casquette_commando.jpg',
                'stock' => 30,
                'category' => 'accessoire',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sac à dos tactique 40L',
                'description' => 'Sac renforcé, plusieurs compartiments, sangles réglables.',
                'price' => 60000,
                'image' => 'sac_tactique.jpg',
                'stock' => 12,
                'category' => 'sac',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}