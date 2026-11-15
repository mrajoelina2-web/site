<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/svg+xml" href="{{ asset('image/logo-icon.svg') }}">
<title>Équip'Style — Achats</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; margin: 0; padding: 30px; }
    h1 { text-align: center; color: #a3b18a; }
    h2 { text-align: center; color: #a3b18a; margin-top: 40px; }
    .stats { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin: 20px 0; }
    .stat {
        background: rgba(58, 63, 51, 0.85); border: 1px solid #565c4b;
        border-radius: 10px; padding: 14px 24px; text-align: center;
    }
    .stat strong { display: block; font-size: 22px; color: #a3b18a; }
    .table-wrap { max-width: 1000px; margin: 0 auto; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; background: rgba(58, 63, 51, 0.85); border-radius: 10px; overflow: hidden; }
    th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #565c4b; white-space: nowrap; }
    td.items { white-space: normal; min-width: 240px; }
    th { background: #1f2318; color: #a3b18a; }
    .tag-new { background:#e94560; color:white; font-size:11px; font-weight:bold; border-radius:10px; padding:2px 8px; margin-left:6px; }
    .note { max-width: 1000px; margin: 8px auto 0; font-size: 13px; color: #9c9c8f; }
</style>
</head>
<body>

<x-video-bg />
<x-nav />
<x-toast />

<h1>📦 Tous les achats</h1>

<div class="stats">
    <div class="stat"><strong>{{ $nbCommandes }}</strong>commande(s)</div>
    <div class="stat"><strong>{{ number_format($totalVentes, 0, ',', ' ') }} Ar</strong>total des ventes</div>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>N°</th>
            <th>Date</th>
            <th>Client</th>
            <th>Articles achetés</th>
            <th>Total</th>
        </tr>
        @forelse($orders as $order)
            <tr>
                <td>
                    #{{ $order->id }}
                    @if(in_array($order->id, $newOrderIds))
                        <span class="tag-new">Nouveau</span>
                    @endif
                </td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    {{ $order->user->name ?? 'Compte supprimé' }}<br>
                    <small style="color:#9c9c8f;">{{ $order->user->email ?? '-' }}</small>
                </td>
                <td class="items">
                    @forelse($order->items as $item)
                        {{ $item->quantity }} × {{ $item->product_name }}@if($item->option) ({{ $item->option }})@endif<br>
                    @empty
                        <em style="color:#9c9c8f;">Détail non enregistré ({{ $order->nb_articles }} article(s))</em>
                    @endforelse
                </td>
                <td>{{ number_format($order->total, 0, ',', ' ') }} Ar</td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;">Aucun achat pour le moment.</td></tr>
        @endforelse
    </table>
</div>

<h2>🏆 Articles vendus</h2>

<div class="table-wrap">
    <table>
        <tr>
            <th>Article</th>
            <th>Quantité vendue</th>
            <th>Montant</th>
        </tr>
        @forelse($ventesParArticle as $vente)
            <tr>
                <td>{{ $vente->product_name }}</td>
                <td>{{ $vente->total_qty }}</td>
                <td>{{ number_format($vente->chiffre, 0, ',', ' ') }} Ar</td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align:center;">Aucun article vendu pour le moment.</td></tr>
        @endforelse
    </table>
</div>
<p class="note">Les montants par article sont calculés avant réduction. Le total des ventes ci-dessus tient compte des coupons utilisés, il peut donc être un peu plus bas.</p>

</body>
</html>