<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Équip'Style — Panier</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; padding: 30px; margin: 0; }
    h1 { color: #a3b18a; text-align: center; }
    .cart-container { max-width: 700px; margin: 0 auto; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; background: #3a3f33; padding: 14px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #565c4b; }
    .cart-total { text-align: right; font-size: 20px; margin: 20px 0; color: #a3b18a; }
    .validate-btn { display: block; width: 100%; background: #5c6b3f; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; cursor: pointer; }
    .remove-btn { background: none; border: 1px solid #a3b18a; color: #a3b18a; border-radius: 6px; padding: 6px 10px; cursor: pointer; }
    .message { text-align: center; margin-bottom: 20px; color: #a3b18a; }
</style>
</head>
<body>

<x-video-bg />
<x-nav />
<x-toast />

<div class="cart-container">
    <h1>🛒 Ton panier</h1>

    @if($items->isEmpty())
        <p style="text-align:center">Ton panier est vide. <a href="{{ route('products.index') }}" style="color:#a3b18a">Voir le catalogue</a></p>
    @else
        @foreach($items as $item)
            <div class="cart-item">
                <div>
                    <strong>{{ $item['product']->name }}</strong>
                    @if($item['option'])
                        <br><small style="color:#a3b18a;">{{ $item['option_label'] }} : {{ $item['option'] }}</small>
                    @endif
                    <br>
                    {{ $item['quantity'] }} × {{ number_format($item['unit_price'], 0, ',', ' ') }} Ar
                </div>
                <div>
                    {{ number_format($item['subtotal'], 0, ',', ' ') }} Ar
                    <form action="{{ route('cart.remove') }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                        <button type="submit" class="remove-btn">Retirer</button>
                    </form>
                </div>
            </div>
        @endforeach

        @if($availableCoupons->isNotEmpty() && $discountPercent == 0)
            <div style="margin-bottom:14px;">
                <p style="color:#9c9c8f; font-size:14px; margin-bottom:8px;">Tes coupons disponibles :</p>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($availableCoupons as $coupon)
                        <form action="{{ route('cart.applyCoupon') }}" method="POST">
                            @csrf
                            <input type="hidden" name="code" value="{{ $coupon->code }}">
                            <button type="submit" style="background:#3a3f33; border:1px solid #a3b18a; color:#a3b18a; padding:8px 14px; border-radius:20px; cursor:pointer; font-size:13px;">
                                -{{ $coupon->discount_percent }}% ({{ $coupon->code }})
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif

        @if($discountPercent > 0)
            <div style="background:#3a3f33; padding:12px; border-radius:8px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                <span>✅ Coupon <strong>{{ $couponCode }}</strong> appliqué : -{{ $discountPercent }}%</span>
                <form action="{{ route('cart.removeCoupon') }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="remove-btn">Retirer</button>
                </form>
            </div>
        @else
            <form action="{{ route('cart.applyCoupon') }}" method="POST" style="display:flex; gap:8px; margin-bottom:14px;">
                @csrf
                <input type="text" name="code" placeholder="Code coupon (ex: SPIN-AB12CD)" style="flex:1; padding:10px; border-radius:6px; border:1px solid #565c4b; background:#3a3f33; color:#eae7dc;">
                <button type="submit" class="remove-btn" style="border-color:#a3b18a; color:#a3b18a;">Appliquer</button>
            </form>
        @endif

        @if($discountPercent > 0)
            <p style="text-align:right; color:#9c9c8f;">Sous-total : {{ number_format($total, 0, ',', ' ') }} Ar</p>
            <p style="text-align:right; color:#e94560;">Réduction (-{{ $discountPercent }}%) : -{{ number_format($discountAmount, 0, ',', ' ') }} Ar</p>
            <p class="cart-total">Total à payer : {{ number_format($totalAfterDiscount, 0, ',', ' ') }} Ar — {{ $nbArticles }} article(s)</p>
        @else
            <p class="cart-total">Total : {{ number_format($total, 0, ',', ' ') }} Ar — {{ $nbArticles }} article(s)</p>
        @endif

        @if($nbArticles >= 2)
            <p style="text-align:center; color:#a3b18a;">🎖️ Achète ces articles pour débloquer un spin !</p>
        @endif

        <form action="{{ route('checkout.validate') }}" method="POST">
            @csrf
            <button type="submit" class="validate-btn">Valider l'achat</button>
        </form>
    @endif
</div>

</body>
</html>