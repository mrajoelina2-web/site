<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Spin;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckoutController extends Controller
{
    public function validate()
    {
        $lines = Cart::lines();

        if (empty($lines)) {
            return redirect()->route('products.index')->with('error', 'Ton panier est vide.');
        }

        $nbArticles = Cart::count();

        // Étape spin obligatoire avant de finaliser, une seule fois par panier
        if ($nbArticles >= 2 && !session('pending_spin_done')) {
            $spin = Spin::firstOrCreate(
                ['user_id' => auth()->id()],
                ['available_spins' => 0]
            );
            $spin->increment('available_spins');

            session(['pending_spin_done' => true]);

            return redirect()->route('spin.spin')
                ->with('success', 'Tourne la roue, puis reviens à ton panier pour valider ton achat !');
        }

        $products = Product::whereIn('id', collect($lines)->pluck('product_id')->unique())->get()->keyBy('id');

        // Total du panier et quantité demandée par article (toutes options confondues)
        $total = 0;
        $qtyParProduit = [];

        foreach ($lines as $line) {
            $product = $products->get($line['product_id']);

            if (!$product) {
                continue;
            }

            $total += Cart::unitPrice($product, $line['option']) * $line['qty'];
            $qtyParProduit[$product->id] = ($qtyParProduit[$product->id] ?? 0) + $line['qty'];
        }

        if (empty($qtyParProduit)) {
            return redirect()->route('cart.index')->with('error', "Les articles de ton panier ne sont plus disponibles.");
        }

        $couponCode = session('coupon_code');
        $coupon = null;
        $discountPercent = 0;

        if ($couponCode) {
            $coupon = Coupon::where('user_id', auth()->id())
                ->where('code', $couponCode)
                ->where('used', false)
                ->first();

            if ($coupon) {
                $discountPercent = $coupon->discount_percent;
            }
        }

        $totalFinal = $total - ($total * $discountPercent / 100);

        try {
            $order = DB::transaction(function () use ($lines, $products, $qtyParProduit, $nbArticles, $totalFinal, $coupon) {
                // Verrouille les articles et vérifie le stock
                $locked = Product::whereIn('id', array_keys($qtyParProduit))->lockForUpdate()->get()->keyBy('id');

                foreach ($qtyParProduit as $id => $qty) {
                    $p = $locked->get($id);

                    if (!$p || $p->stock < $qty) {
                        throw new \RuntimeException('Stock insuffisant pour « ' . ($p->name ?? 'un article') . ' ».');
                    }
                }

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'nb_articles' => $nbArticles,
                    'total' => $totalFinal,
                ]);

                foreach ($lines as $line) {
                    $product = $products->get($line['product_id']);

                    if (!$product) {
                        continue;
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'option' => $line['option'],
                        'unit_price' => Cart::unitPrice($product, $line['option']),
                        'quantity' => $line['qty'],
                    ]);
                }

                foreach ($qtyParProduit as $id => $qty) {
                    $locked->get($id)->decrement('stock', $qty);
                }

                if ($coupon) {
                    $coupon->update(['used' => true]);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        // Notifie les administrateurs (sauf si l'acheteur est lui-même admin)
        try {
            $admins = User::where('is_admin', true)
                ->where('id', '!=', auth()->id())
                ->get();

            Notification::send($admins, new NewOrderNotification($order, auth()->user()));
        } catch (\Throwable $e) {
            report($e);
        }

        session()->forget(['cart', 'coupon_code', 'pending_spin_done']);

        return redirect()->route('products.index')->with('success', 'Achat validé avec succès !');
    }
}