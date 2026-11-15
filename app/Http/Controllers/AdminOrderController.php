<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;

class AdminOrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $newOrderIds = $user->unreadNotifications->pluck('data.order_id')->all();
        $user->unreadNotifications->markAsRead();

        $orders = Order::with(['user', 'items'])->orderBy('created_at', 'desc')->get();

        $totalVentes = $orders->sum('total');
        $nbCommandes = $orders->count();

        // Récapitulatif : quels articles se vendent, et combien
        $ventesParArticle = OrderItem::selectRaw('product_name, SUM(quantity) as total_qty, SUM(quantity * unit_price) as chiffre')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->get();

        return view('admin.orders.index', compact(
            'orders', 'totalVentes', 'nbCommandes', 'newOrderIds', 'ventesParArticle'
        ));
    }
}
