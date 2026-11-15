<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-icon.svg') }}">
<title>Équip'Style — {{ ucfirst($category) }}</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; margin: 0; padding: 30px; }
    h1 { text-align: center; color: #a3b18a; margin-bottom: 10px; letter-spacing: 1px; text-transform: capitalize; }

    .back-link {
        display: block;
        text-align: center;
        color: #a3b18a;
        text-decoration: none;
        margin-bottom: 24px;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 24px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .product-card {
        background: rgba(58, 63, 51, 0.85);
        backdrop-filter: blur(4px);
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid #565c4b;
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .product-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        border-color: #a3b18a;
    }

    .product-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: #4a4f3e;
        transition: transform 0.4s ease;
    }

    .product-card:hover img {
        transform: scale(1.08);
    }

    .product-info {
        padding: 14px 14px 18px 14px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .product-info h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        transition: color 0.3s ease;
    }

    .product-card:hover .product-info h3 {
        color: #a3b18a;
    }

    .product-price {
        color: #a3b18a;
        font-weight: bold;
        font-size: 18px;
        margin: 8px 0;
    }

    .product-stock {
        font-size: 13px;
        color: #9c9c8f;
        margin-bottom: 12px;
    }

    .add-to-cart-btn {
        margin-top: 14px;
        background: #5c6b3f;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        width: 100%;
        transition: background 0.3s ease;
    }

    .add-to-cart-btn:hover:not(:disabled) {
        background: #7a8c54;
    }

    .add-to-cart-btn:disabled {
        background: #555;
        cursor: not-allowed;
    }

    .empty-msg {
        text-align: center;
        color: #9c9c8f;
    }

    .option-select {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #565c4b;
    background: #2b2f26;
    color: #eae7dc;
    font-size: 14px;
    }
</style>
</head>
<body>

<x-video-bg />
<x-nav />
<x-toast />

<a href="{{ route('products.index') }}" class="back-link">← Retour aux catégories</a>
<h1>🎖️ {{ $category }}</h1>

<div class="products-grid">
    @forelse($products as $product)
        <div class="product-card">
            <img src="{{ asset('image/' . $product->image) }}" alt="{{ $product->name }}">
            <div class="product-info">
                <h3>{{ $product->name }}</h3>
                <p class="product-price">{{ number_format($product->price, 0, ',', ' ') }} Ar</p>
                <p class="product-stock">
                    @if($product->stock > 0)
                        En stock ({{ $product->stock }})
                    @else
                        Rupture de stock
                    @endif
                </p>
                @auth
                    @php $opt = config('options.' . $product->category); @endphp
                    <form action="{{ route('cart.add', $product) }}" method="POST">
                        @csrf

                        @if($opt)
                            <select name="option" required class="option-select">
                                <option value="" disabled selected>{{ $opt['label'] }}...</option>
                                @foreach($opt['choices'] as $choice)
                                    @php $extra = $opt['extra'][$choice] ?? 0; @endphp
                                    <option value="{{ $choice }}">
                                        {{ $choice }}{{ $extra > 0 ? ' (+' . number_format($extra, 0, ',', ' ') . ' Ar)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <button type="submit" class="add-to-cart-btn" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        Ajouter au panier
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="add-to-cart-btn" style="text-align:center; text-decoration:none; display:block;">
                        Se connecter pour acheter
                    </a>
                @endauth
            </div>
        </div>
    @empty
        <p class="empty-msg">Aucun article dans cette catégorie pour le moment.</p>
    @endforelse
</div>

</body>
</html>