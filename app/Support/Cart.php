<?php

namespace App\Support;

class Cart
{
    // Lignes du panier : chaque ligne = un article + son option (taille, pointure, niveau)
    public static function lines(): array
    {
        $lines = [];

        foreach (session('cart', []) as $key => $line) {
            if (is_array($line)) {
                $lines[$key] = $line;
            } else {
                // Ancien format (id => quantité) : on le convertit
                $lines[$key . '|'] = ['product_id' => (int) $key, 'option' => null, 'qty' => (int) $line];
            }
        }

        return $lines;
    }

    public static function save(array $lines): void
    {
        session(['cart' => $lines]);
    }

    public static function count(): int
    {
        return (int) array_sum(array_column(self::lines(), 'qty'));
    }

    public static function key(int $productId, ?string $option): string
    {
        return $productId . '|' . ($option ?? '');
    }

    // Prix unitaire = prix de l'article + éventuel supplément de l'option
    public static function unitPrice($product, ?string $option): float
    {
        $extra = 0;

        if ($option !== null) {
            $extra = (float) (config('options.' . $product->category . '.extra', [])[$option] ?? 0);
        }

        return (float) $product->price + $extra;
    }
}