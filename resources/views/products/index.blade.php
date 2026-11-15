<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Équip'Style — Catalogue</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; margin: 0; padding: 30px; }    
    h1 { text-align: center; color: #a3b18a; margin-bottom: 30px; letter-spacing: 1px; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; max-width: 1100px; margin: 0 auto; }
    .product-card { background: #3a3f33; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; border: 1px solid #565c4b; transition: transform 0.2s; }
    .product-card:hover { transform: translateY(-4px); }
    .product-card {
    background: rgba(58, 63, 51, 0.35);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    
    }
    .product-info {
        padding: 14px 14px 18px 14px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }    
    .product-info h3 { margin: 0 0 8px 0; font-size: 16px; }
    .product-price { color: #a3b18a; font-weight: bold; font-size: 18px; margin: 8px 0; }
    .product-stock { font-size: 13px; color: #9c9c8f; margin-bottom: 12px; }
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
    .add-to-cart-btn:disabled { background: #555; cursor: not-allowed; }
    .top-bar { max-width: 1100px; margin: 0 auto 20px auto; display: flex; justify-content: flex-end; }
    .top-bar a { color: #a3b18a; text-decoration: none; }
    @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
    }

    body.animated-bg {
    background: linear-gradient(-45deg, #ff0000, #00ff00, #0000ff, #ff0000);
    background-size: 400% 400%;
    animation: gradientShift 5s ease infinite;
}
</style>
</head>
<body>
<x-video-bg />
<x-nav />
<x-toast />
<h1>🎖️ Catalogue — Équip'Style</h1>

<div class="products-grid">
    @foreach($products as $product)
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
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="add-to-cart-btn" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        Ajouter au panier
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>

</body>
</html>