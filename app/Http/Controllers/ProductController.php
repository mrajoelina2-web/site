<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    // Page d'accueil : affiche les catégories disponibles
    public function index()
    {
        $categories = Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->get()
            ->map(function ($item) {
                $products = Product::where('category', $item->category)->get();

                // Cherche un produit avec une vraie photo (hors visuels de secours cat_*.svg)
                $withPhoto = $products->first(function ($p) {
                    return $p->image
                        && !str_starts_with($p->image, 'cat_')
                        && file_exists(public_path('image/' . $p->image));
                });

                // Sinon, retombe sur l'icône de la catégorie
                $image = $withPhoto->image ?? 'cat_' . $item->category . '.svg';

                return [
                    'name' => $item->category,
                    'image' => $image,
                    'count' => $products->count(),
                ];
            });

        return view('products.categories', compact('categories'));
    }

    // Liste des produits d'une catégorie précise
    public function byCategory($category)
    {
        $products = Product::where('category', $category)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('products.category', compact('products', 'category'));
    }
}