<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/svg+xml" href="{{ asset('image/logo-icon.svg') }}">
<title>Équip'Style — Bilan des ventes</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; margin: 0; padding: 30px; }
    h1 { text-align: center; color: #a3b18a; }
    h2 { color: #a3b18a; margin: 0 0 16px 0; font-size: 18px; }

    .periods { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin: 16px 0; }
    .periods a {
        color: #a3b18a; text-decoration: none; border: 1px solid #a3b18a;
        border-radius: 20px; padding: 6px 14px; font-size: 14px;
    }
    .periods a.active { background: #5c6b3f; color: white; border-color: #5c6b3f; }

    .year-form { text-align: center; margin-bottom: 10px; }
    .year-form select {
        background: #2b2f26; color: #eae7dc; border: 1px solid #565c4b;
        border-radius: 8px; padding: 6px 12px; font-size: 14px;
    }

    .kpis { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin: 20px 0; }
    .kpi {
        background: rgba(58, 63, 51, 0.85); border: 1px solid #565c4b;
        border-radius: 10px; padding: 14px 24px; text-align: center; min-width: 150px;
    }
    .kpi strong { display: block; font-size: 22px; color: #a3b18a; }
    .kpi small { color: #9c9c8f; }
    .up { color: #7bd88f !important; }
    .down { color: #e94560 !important; }

    .card {
        max-width: 1000px; margin: 0 auto 30px;
        background: rgba(58, 63, 51, 0.85); border: 1px solid #565c4b;
        border-radius: 12px; padding: 20px;
    }

    .bar-row { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
    .rank { width: 36px; text-align: center; font-size: 22px; flex-shrink: 0; }
    .bar-body { flex: 1; min-width: 0; }
    .bar-label { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 4px; font-size: 14px; }
    .bar-track { background: #1f2318; border-radius: 8px; height: 16px; overflow: hidden; }
    .bar-fill { background: linear-gradient(90deg, #7a8c54, #a3b18a); height: 100%; border-radius: 8px; }

    .months { display: flex; gap: 6px; height: 240px; }
    .month-col { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .month-bar-wrap { flex: 1; display: flex; align-items: flex-end; }
    .month-bar { width: 100%; background: linear-gradient(0deg, #7a8c54, #a3b18a); border-radius: 6px 6px 0 0; min-height: 2px; }
    .month-name { text-align: center; font-size: 12px; margin-top: 6px; }
    .month-val { text-align: center; font-size: 11px; color: #9c9c8f; }

    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #565c4b; white-space: nowrap; }
    th { background: #1f2318; color: #a3b18a; }
    tr.total-row td { font-weight: bold; border-top: 2px solid #a3b18a; }
    .low { color: #e94560; font-weight: bold; }

    .note { max-width: 1000px; margin: 0 auto 20px; font-size: 13px; color: #9c9c8f; }
    .print-btn {
        display: block; margin: 0 auto 30px; background: none; border: 1px solid #a3b18a;
        color: #a3b18a; border-radius: 8px; padding: 8px 18px; cursor: pointer; font-size: 14px;
    }

    .doc-header {
    max-width: 1000px; margin: 0 auto 20px; padding-bottom: 14px;
    display: flex; align-items: center; gap: 20px;
    border-bottom: 2px solid #a3b18a;
}
    .doc-logo { height: 80px; border-radius: 8px; }
    .doc-title h1 { margin: 0 0 6px 0; text-align: left; }
    .doc-title p { margin: 2px 0; font-size: 14px; color: #eae7dc; }

    .doc-footer { max-width: 1000px; margin: 30px auto; break-inside: avoid; }
    .signature-block { width: 300px; margin-left: auto; font-size: 14px; }
    .signature-block p { margin: 8px 0; }
    .sig-title { text-align: center; font-weight: bold; margin-top: 18px !important; }
    .sig-box { height: 110px; border: 1px dashed #a3b18a; border-radius: 6px; }
    
    @media (max-width: 600px) {
        body { padding: 16px; }
        .months { gap: 3px; }
        .month-val { display: none; }
        .month-name { font-size: 10px; }
    }

    @media print {
        .site-nav, .video-bg-container, #cw-toggle, #cw-panel, .no-print { display: none !important; }
        body { background: white !important; color: black !important; padding: 10px; }
        h1, h2, th, .kpi strong, .periods a { color: black !important; }
        .card, .kpi { background: white !important; border-color: #999 !important; }
        .card *, .kpi * { color: black !important; }
        th { background: #eee !important; }
        .bar-track { background: #ddd !important; }
        .bar-fill, .month-bar { background: #444 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .doc-header, .doc-header *, .doc-footer, .doc-footer * { color: black !important; }
        .doc-header { border-bottom-color: #666 !important; }
        .sig-box { border-color: #000 !important; }
    }
</style>
</head>
<body>

<x-video-bg />
<x-nav />
<x-toast />

@php
    $libelle = match ($periode) {
        'annee' => 'Année ' . $annee,
        '7j' => '7 derniers jours',
        '30j' => '30 derniers jours',
        'mois' => 'Mois en cours',
        default => 'Toutes les ventes',
    };
@endphp

<div class="doc-header">
    <img src="{{ asset('image/logo.jpeg') }}" alt="Équip'Style" class="doc-logo">
    <div class="doc-title">
        <h1>{{ $periode === 'annee' ? 'Bilan annuel ' . $annee : 'Bilan des ventes' }}</h1>
        <p>Équip'Style · Période : {{ $libelle }}</p>
        <p>Date d'enregistrement : {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</div>
<div class="periods no-print">
    <a href="{{ route('admin.report.index') }}" class="{{ $periode === 'tout' ? 'active' : '' }}">Tout</a>
    <a href="{{ route('admin.report.index', ['periode' => '7j']) }}" class="{{ $periode === '7j' ? 'active' : '' }}">7 derniers jours</a>
    <a href="{{ route('admin.report.index', ['periode' => '30j']) }}" class="{{ $periode === '30j' ? 'active' : '' }}">30 derniers jours</a>
    <a href="{{ route('admin.report.index', ['periode' => 'mois']) }}" class="{{ $periode === 'mois' ? 'active' : '' }}">Ce mois-ci</a>
    <a href="{{ route('admin.report.index', ['periode' => 'annee', 'annee' => $annee]) }}" class="{{ $periode === 'annee' ? 'active' : '' }}">Bilan annuel</a>
</div>

@if($periode === 'annee')
    <form method="GET" action="{{ route('admin.report.index') }}" class="year-form no-print">
        <input type="hidden" name="periode" value="annee">
        <label for="annee">Année :</label>
        <select name="annee" id="annee" onchange="this.form.submit()">
            @foreach($annees as $a)
                <option value="{{ $a }}" {{ $a == $annee ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </form>
@endif

<div class="kpis">
    <div class="kpi"><strong>{{ $nbCommandes }}</strong><small>commande(s)</small></div>
    <div class="kpi"><strong>{{ $articlesVendus }}</strong><small>article(s) vendu(s)</small></div>
    <div class="kpi"><strong>{{ number_format($encaisse, 0, ',', ' ') }} Ar</strong><small>total des commandes (après coupons)</small></div>

    @if($periode === 'annee')
        <div class="kpi">
            @if($evolution !== null)
                <strong class="{{ $evolution >= 0 ? 'up' : 'down' }}">{{ $evolution > 0 ? '+' : '' }}{{ number_format($evolution, 1, ',', ' ') }} %</strong>
                <small>par rapport à {{ $annee - 1 }}</small>
            @else
                <strong>-</strong>
                <small>pas de ventes en {{ $annee - 1 }} à comparer</small>
            @endif
        </div>
        <div class="kpi">
            @if($meilleurMois)
                <strong>{{ $meilleurMois['nom'] }}</strong>
                <small>meilleur mois ({{ number_format($meilleurMois['total'], 0, ',', ' ') }} Ar)</small>
            @else
                <strong>-</strong>
                <small>meilleur mois</small>
            @endif
        </div>
    @endif
</div>

@if($periode === 'annee')
    <div class="card">
        <h2>📅 Ventes mois par mois en {{ $annee }}</h2>

        <div class="months">
            @foreach($parMois as $m)
                <div class="month-col" title="{{ $m['nom'] }} : {{ number_format($m['total'], 0, ',', ' ') }} Ar">
                    <div class="month-val">{{ $m['total'] > 0 ? number_format($m['total'] / 1000, 0, ',', ' ') . ' k' : '' }}</div>
                    <div class="month-bar-wrap">
                        <div class="month-bar" style="height: {{ round($m['total'] / $maxMois * 100) }}%"></div>
                    </div>
                    <div class="month-name">{{ mb_substr($m['nom'], 0, 3) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2>Détail par mois</h2>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Mois</th>
                    <th>Commandes</th>
                    <th>Articles</th>
                    <th>Total</th>
                </tr>
                @foreach($parMois as $m)
                    <tr>
                        <td>{{ $m['nom'] }}</td>
                        <td>{{ $m['commandes'] }}</td>
                        <td>{{ $m['articles'] }}</td>
                        <td>{{ number_format($m['total'], 0, ',', ' ') }} Ar</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total {{ $annee }}</td>
                    <td>{{ $nbCommandes }}</td>
                    <td>{{ $articlesVendus }}</td>
                    <td>{{ number_format($encaisse, 0, ',', ' ') }} Ar</td>
                </tr>
            </table>
        </div>
    </div>
@endif

<div class="card">
    <h2>🏆 Top 5 des articles les plus vendus{{ $periode === 'annee' ? ' en ' . $annee : '' }}</h2>

    @forelse($top as $i => $ligne)
        <div class="bar-row">
            <span class="rank">{{ ['🥇', '🥈', '🥉'][$i] ?? ($i + 1) }}</span>
            <div class="bar-body">
                <div class="bar-label">
                    <span>{{ $ligne->product_name }}</span>
                    <span>{{ $ligne->total_qty }} vendu(s)</span>
                </div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ round($ligne->total_qty / $maxQty * 100) }}%"></div>
                </div>
            </div>
        </div>
    @empty
        <p>Aucune vente sur cette période.</p>
    @endforelse
</div>

<div class="card">
    <h2>Classement complet</h2>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Rang</th>
                <th>Article</th>
                <th>Quantité vendue</th>
                <th>Part des ventes</th>
                <th>Montant (avant réduction)</th>
                <th>Stock restant</th>
            </tr>
            @forelse($classement as $i => $ligne)
                @php
                    $stock = $ligne->product_id ? ($stocks[$ligne->product_id] ?? null) : null;
                    $part = $totalClasse > 0 ? round($ligne->total_qty / $totalClasse * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $ligne->product_name }}</td>
                    <td>{{ $ligne->total_qty }}</td>
                    <td>{{ $part }} %</td>
                    <td>{{ number_format($ligne->chiffre, 0, ',', ' ') }} Ar</td>
                    <td>
                        @if($stock === null)
                            <span style="color:#9c9c8f;">article supprimé</span>
                        @elseif($stock <= 3)
                            <span class="low">{{ $stock }} (faible)</span>
                        @else
                            {{ $stock }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">Aucune vente sur cette période.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<p class="note">Les chiffres clés et le tableau par mois comptent toutes les commandes. Le classement des articles ne compte que les commandes enregistrées avec leur détail, donc pas celles passées avant l'ajout de ce détail.</p>
<div class="doc-footer">
    <div class="signature-block">
        <p>Fait à : ______________________</p>
        <p>Le : ____ / ____ / ________</p>
        <p class="sig-title">Le responsable</p>
        <div class="sig-box"></div>
        <p>Nom et prénom : ____________________</p>
        <p>Signature</p>
    </div>
</div>
<button class="print-btn no-print" onclick="window.print()">🖨 Imprimer ou enregistrer en PDF</button>


</body>
</html>