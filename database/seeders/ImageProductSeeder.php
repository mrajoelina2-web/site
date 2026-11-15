<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ImageProductSeeder extends Seeder
{
    public function run(): void
    {
        // dossier dans public/image/produits => [catégorie en base, préfixe du nom, prix de base en Ariary]
        $dossiers = [
            'sac' => ['category' => 'sac', 'prefix' => 'Sac tactique', 'price' => 150000],
            'survet' => ['category' => 'survetement', 'prefix' => 'Survêtement militaire', 'price' => 180000],
            'pantalon' => ['category' => 'pantalon', 'prefix' => 'Pantalon tactique', 'price' => 130000],
            'vest' => ['category' => 'vest', 'prefix' => 'vest', 'price' => 130000],
            'bottes' => ['category' => 'bottes', 'prefix' => 'bottes', 'price' => 130000],
            'casquette' => ['category' => 'casquette', 'prefix' => 'casquette', 'price' => 130000],
        ];

        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        foreach ($dossiers as $dossier => $config) {
            $chemin = public_path('image/produits/' . $dossier);

            if (!is_dir($chemin)) {
                $this->command->warn("Dossier introuvable : $chemin");
                continue;
            }

            $fichiers = collect(scandir($chemin))
                ->filter(fn($f) => !in_array($f, ['.', '..']))
                ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $extensions))
                ->values();

            foreach ($fichiers as $index => $fichier) {
                $imagePath = 'produits/' . $dossier . '/' . $fichier;

                Product::firstOrCreate(
                    ['image' => $imagePath],
                    [
                        'name' => $config['prefix'] . ' #' . ($index + 1),
                        'description' => 'Article de style militaire, qualité renforcée.',
                        'price' => $config['price'] + ($index * 5000),
                        'stock' => 15,
                        'category' => $config['category'],
                    ]
                );
            }

            $this->command->info("$dossier : " . $fichiers->count() . ' image(s) traitée(s).');
        }
    }
}