<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->query('periode', 'tout');
        $anneeCourante = now()->year;
        $annee = max(2000, min(2100, (int) $request->query('annee', $anneeCourante)));

        $depuis = null;
        $jusqua = null;

        if ($periode === 'annee') {
            $depuis = Carbon::create($annee, 1, 1)->startOfDay();
            $jusqua = Carbon::create($annee, 12, 31)->endOfDay();
        } elseif ($periode === '7j') {
            $depuis = now()->subDays(7);
        } elseif ($periode === '30j') {
            $depuis = now()->subDays(30);
        } elseif ($periode === 'mois') {
            $depuis = now()->startOfMonth();
        }

        // Classement des articles vendus (tri par quantité, puis par montant)
        $classement = OrderItem::query()
            ->when($depuis, fn ($q) => $q->where('created_at', '>=', $depuis))
            ->when($jusqua, fn ($q) => $q->where('created_at', '<=', $jusqua))
            ->selectRaw('product_name, MAX(product_id) as product_id, SUM(quantity) as total_qty, SUM(quantity * unit_price) as chiffre')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->orderByDesc('chiffre')
            ->get();

        // Chiffres clés : toutes les commandes de la période
        $commandes = Order::query()
            ->when($depuis, fn ($q) => $q->where('created_at', '>=', $depuis))
            ->when($jusqua, fn ($q) => $q->where('created_at', '<=', $jusqua));

        $nbCommandes = (clone $commandes)->count();
        $encaisse = (clone $commandes)->sum('total');
        $articlesVendus = (int) (clone $commandes)->sum('nb_articles');

        // Base du calcul des parts dans le classement (commandes avec détail)
        $totalClasse = (int) $classement->sum('total_qty');

        $stocks = Product::whereIn('id', $classement->pluck('product_id')->filter())->pluck('stock', 'id');

        $top = $classement->take(5);
        $maxQty = max(1, (int) ($top->first()->total_qty ?? 1));

        // Bilan annuel : ventes mois par mois
        $parMois = [];
        $evolution = null;
        $meilleurMois = null;

        if ($periode === 'annee') {
            $noms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

            // Regroupement en PHP : fonctionne aussi bien avec SQLite qu'avec MySQL
            $commandesAnnee = Order::whereBetween('created_at', [$depuis, $jusqua])
                ->get(['id', 'total', 'nb_articles', 'created_at'])
                ->groupBy(fn ($o) => (int) $o->created_at->format('n'));

            for ($m = 1; $m <= 12; $m++) {
                $groupe = $commandesAnnee->get($m, collect());
                $parMois[] = [
                    'nom' => $noms[$m - 1],
                    'commandes' => $groupe->count(),
                    'articles' => (int) $groupe->sum('nb_articles'),
                    'total' => $groupe->sum('total'),
                ];
            }

            // Évolution par rapport à l'année précédente
            $precedent = Order::whereBetween('created_at', [
                Carbon::create($annee - 1, 1, 1)->startOfDay(),
                Carbon::create($annee - 1, 12, 31)->endOfDay(),
            ])->sum('total');

            if ($precedent > 0) {
                $evolution = round(($encaisse - $precedent) / $precedent * 100, 1);
            }

            $meilleur = collect($parMois)->sortByDesc('total')->first();
            $meilleurMois = ($meilleur && $meilleur['total'] > 0) ? $meilleur : null;
        }

        $maxMois = max(1, (float) (collect($parMois)->max('total') ?? 1));

        // Années proposées dans la liste déroulante
        $premiere = Order::min('created_at');
        $premiereAnnee = $premiere ? Carbon::parse($premiere)->year : $anneeCourante;
        $annees = range($anneeCourante, min($premiereAnnee, $anneeCourante));

        return view('admin.report.index', compact(
            'periode', 'annee', 'annees', 'classement', 'top', 'maxQty', 'stocks',
            'nbCommandes', 'encaisse', 'articlesVendus', 'totalClasse',
            'parMois', 'maxMois', 'evolution', 'meilleurMois'
        ));
    }
}