<?php
    $cartCount = \App\Support\Cart::count();
?>
<style>
    .site-nav {
        background: rgba(31, 35, 24, 0.9);
        padding: 16px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: system-ui, sans-serif;
        position: sticky;
        top: 0;
        z-index: 10;
        width: 100%;
        box-sizing: border-box;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .site-nav a { color: #eae7dc; text-decoration: none; }
    .site-nav .brand img { height: 40px; display: block; }
    .nav-links { display: flex; gap: 24px; align-items: center; }
    .nav-cart { position: relative; }
    .cart-badge {
        background: #e94560; color: white; font-size: 11px; font-weight: bold;
        border-radius: 50%; padding: 2px 6px; position: absolute;
        top: -10px; right: -14px; min-width: 16px; text-align: center; line-height: 1.4;
    }
    .logout-btn { background: none; border: none; color: #a3b18a; cursor: pointer; font-size: 14px; padding: 0; }
    .nav-toggle {
        display: none; background: none; border: 1px solid #a3b18a; color: #a3b18a;
        border-radius: 6px; padding: 6px 12px; font-size: 20px; cursor: pointer;
    }

    @media (max-width: 768px) {
        .site-nav { padding: 12px 16px; flex-wrap: wrap; }
        .nav-toggle { display: block; }
        .nav-links { display: none; flex-direction: column; align-items: flex-start; width: 100%; gap: 14px; padding-top: 14px; }
        .nav-links.open { display: flex; }
    }
</style>

<nav class="site-nav">
    <a href="{{ route('products.index') }}" class="brand">
        <img src="{{ asset('image/logo.jpeg') }}" alt="Équip'Style">
    </a>

    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">☰</button>

    <div class="nav-links" id="nav-links">
        <a href="{{ route('products.index') }}">Catalogue</a>

        @auth
            <a href="{{ route('cart.index') }}" class="nav-cart">
                Panier
                @if($cartCount > 0)
                    <span class="cart-badge">{{ $cartCount }}</span>
                @endif
            </a>
            <a href="{{ route('spin.spin') }}">Spin</a>
            <a href="{{ route('profile.edit') }}">Mon profil</a>

            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.orders.index') }}">Achats</a>
                <a href="{{ route('admin.report.index') }}"> Bilan</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="logout-btn">Se déconnecter</button>
            </form>
        @else
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
        @endauth
    </div>
</nav>

<script>
    document.getElementById('nav-toggle').addEventListener('click', function () {
        document.getElementById('nav-links').classList.toggle('open');
    });
</script>
<x-chat-widget />