<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/svg+xml" href="{{ asset('image/logo-icon.svg') }}">
<title>Équip'Style — Catégories</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; margin: 0; padding: 30px; }
    h1 { text-align: center; color: #a3b18a; margin-bottom: 30px; letter-spacing: 1px; }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 24px;
        max-width: 1000px;
        margin: 0 auto;
    }

    .category-card {
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        height: 200px;
        display: flex;
        align-items: flex-end;
        text-decoration: none;
        border: 1px solid #565c4b;
        box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        transition: transform 0.2s ease;
    }

    .category-card:hover {
        transform: translateY(-4px);
    }

    .category-card img {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.55);
    }

    .category-card .overlay {
        position: relative;
        z-index: 2;
        padding: 18px;
        width: 100%;
        background: linear-gradient(to top, rgba(20,22,16,0.85), transparent);
    }

    .category-card h3 {
        margin: 0 0 4px 0;
        color: #eae7dc;
        font-size: 20px;
        text-transform: capitalize;
    }

    .category-card span {
        color: #a3b18a;
        font-size: 13px;
    }
</style>
</head>
<body>

<x-video-bg />
<x-nav />
<x-toast />

<h1>🎖️ Choisis une catégorie</h1>

<div class="categories-grid">
    @foreach($categories as $cat)
        <a href="{{ route('products.category', $cat['name']) }}" class="category-card">
            @if($cat['image'])
                <img src="{{ asset('image/' . $cat['image']) }}" alt="{{ $cat['name'] }}">
            @endif
            <div class="overlay">
                <h3>{{ $cat['name'] }}</h3>
                <span>{{ $cat['count'] }} article(s)</span>
            </div>
        </a>
    @endforeach
</div>

</body>
</html>