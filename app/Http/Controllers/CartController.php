<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $lines = Cart::lines();
        $products = Product::whereIn('id', collect($lines)->pluck('product_id'))->get()->keyBy('id');

        $items = collect($lines)->map(function ($line, $key) use ($products) {
            $product = $products->get($line['product_id']);

            if (!$product) {
                return null;
            }

            $unit = Cart::unitPrice($product, $line['option']);

            return [
                'key' => $key,
                'product' => $product,
                'option' => $line['option'],
                'option_label' => config('options.' . $product->category . '.label'),
                'quantity' => $line['qty'],
                'unit_price' => $unit,
                'subtotal' => $unit * $line['qty'],
            ];
        })->filter()->values();

        $total = $items->sum('subtotal');
        $nbArticles = $items->sum('quantity');

        $discountPercent = 0;
        $couponCode = session('coupon_code');

        if ($couponCode) {
            $coupon = Coupon::where('user_id', auth()->id())
                ->where('code', $couponCode)
                ->where('used', false)
                ->first();

            if ($coupon) {
                $discountPercent = $coupon->discount_percent;
            } else {
                session()->forget('coupon_code');
            }
        }

        $discountAmount = $total * ($discountPercent / 100);
        $totalAfterDiscount = $total - $discountAmount;

        $availableCoupons = Coupon::where('user_id', auth()->id())
            ->where('used', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cart.index', compact(
            'items',
            'total',
            'nbArticles',
            'discountPercent',
            'discountAmount',
            'totalAfterDiscount',
            'couponCode',
            'availableCoupons'
        ));
    }

    public function add(Request $request, Product $product)
    {
        if ($product->stock <= 0) {
            return back()->with('error', 'Cet article est en rupture de stock.');
        }

        // Si la catégorie a des options (taille, pointure, niveau), le choix est obligatoire
        $opts = config('options.' . $product->category);
        $option = null;

        if ($opts) {
            $option = $request->input('option');

            if (!in_array($option, $opts['choices'], true)) {
                return back()->with('error', "Merci de choisir l'option « {$opts['label']} » avant d'ajouter au panier.");
            }
        }

        $lines = Cart::lines();
        $key = Cart::key($product->id, $option);

        // Quantité déjà présente dans le panier pour cet article, toutes options confondues
        $dejaEnPanier = collect($lines)->where('product_id', $product->id)->sum('qty');

        if ($dejaEnPanier + 1 > $product->stock) {
            return back()->with('error', 'Stock insuffisant : il ne reste que ' . $product->stock . ' exemplaire(s).');
        }

        if (isset($lines[$key])) {
            $lines[$key]['qty']++;
        } else {
            $lines[$key] = ['product_id' => $product->id, 'option' => $option, 'qty' => 1];
        }

        Cart::save($lines);
        session()->forget('pending_spin_done');

        return back()->with('success', $product->name . ($option ? " ({$option})" : '') . ' ajouté au panier.');
    }

    public function remove(Request $request)
    {
        $lines = Cart::lines();
        unset($lines[$request->input('key')]);

        Cart::save($lines);
        session()->forget('pending_spin_done');

        return back()->with('success', 'Article retiré du panier.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $coupon = Coupon::where('user_id', auth()->id())
            ->where('code', strtoupper($request->code))
            ->where('used', false)
            ->first();

        if (!$coupon) {
            return back()->with('error', 'Code invalide, déjà utilisé, ou introuvable.');
        }

        session(['coupon_code' => $coupon->code]);

        return back()->with('success', 'Coupon appliqué : -' . $coupon->discount_percent . '%');
    }

    public function removeCoupon()
    {
        session()->forget('coupon_code');

        return back()->with('success', 'Coupon retiré.');
    }
}