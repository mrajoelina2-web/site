<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Spin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpinController extends Controller
{
    private array $rewards = [
        ['index' => 0, 'label' => 'Oups, pas de chance !', 'weight' => 35, 'type' => 'none', 'value' => null],
        ['index' => 1, 'label' => '-10%', 'weight' => 25, 'type' => 'coupon', 'value' => 10],
        ['index' => 2, 'label' => '-25%', 'weight' => 15, 'type' => 'coupon', 'value' => 25],
        ['index' => 3, 'label' => 'Tirage supplémentaire', 'weight' => 12, 'type' => 'extra_spin', 'value' => null],
        ['index' => 4, 'label' => '-50%', 'weight' => 8, 'type' => 'coupon', 'value' => 50],
        ['index' => 5, 'label' => 'Coupon gratuit -100%', 'weight' => 1, 'type' => 'coupon', 'value' => 100],
    ];

    public function index()
    {
        $spin = Spin::firstOrCreate(
            ['user_id' => auth()->id()],
            ['available_spins' => 0]
        );

        return view('spin.spin', compact('spin'));
    }

    public function play()
    {
        return DB::transaction(function () {
            $spin = Spin::where('user_id', auth()->id())->lockForUpdate()->first();

            if (!$spin || $spin->available_spins <= 0) {
                return response()->json(['error' => 'Aucun spin disponible.'], 400);
            }

            $totalWeight = array_sum(array_column($this->rewards, 'weight'));
            $roll = random_int(0, $totalWeight - 1);

            $chosen = null;
            foreach ($this->rewards as $reward) {
                if ($roll < $reward['weight']) {
                    $chosen = $reward;
                    break;
                }
                $roll -= $reward['weight'];
            }

            // Consomme le spin utilisé pour ce tirage
            $spin->decrement('available_spins');

            if ($chosen['type'] === 'coupon') {
                $couponCode = 'SPIN-' . strtoupper(Str::random(6));

                Coupon::create([
                    'user_id' => auth()->id(),
                    'code' => $couponCode,
                    'discount_percent' => $chosen['value'],
                    'used' => false,
                ]);

                $chosen['code'] = $couponCode;

                session(['coupon_code' => $couponCode]);
            }

            if ($chosen['type'] === 'extra_spin') {
                // Rend un spin en plus de celui qu'on vient de consommer
                $spin->increment('available_spins');
            }

            $spin->refresh();
            $chosen['remaining_spins'] = $spin->available_spins;

            return response()->json(['reward' => $chosen]);
        });
    }
}