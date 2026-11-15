<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Spin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function reply(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:300']);

        // Normalisation : minuscules et sans accents
        $text = Str::lower(Str::ascii($data['message']));

        return response()->json($this->answer($text));
    }

    private function has(string $text, array $words): bool
    {
        foreach ($words as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }
        return false;
    }

    private function ar($amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' Ar';
    }

    private function out(string $reply, array $suggestions = [], ?array $link = null): array
    {
        return ['reply' => $reply, 'suggestions' => $suggestions, 'link' => $link];
    }

    private function answer(string $text): array
    {
        $menu = ['Voir les catégories', 'Comment marche la roue ?', 'Comment acheter ?', 'Mes commandes'];

        // 1. Une catégorie est mentionnée (sac, survêtement, ...)
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');
        foreach ($categories as $cat) {
            $aliases = [Str::lower(Str::ascii($cat))];
            if ($aliases[0] === 'survetement') {
                $aliases[] = 'survet';
            }

            if ($this->has($text, $aliases)) {
                $items = Product::where('category', $cat)->orderBy('price')->get();
                $count = $items->count();
                $enStock = $items->where('stock', '>', 0)->count();
                $min = $this->ar($items->min('price'));
                $max = $this->ar($items->max('price'));
                $exemples = $items->take(4)
                    ->map(fn ($p) => $p->name . ' (' . $this->ar($p->price) . ')')
                    ->implode(', ');

                return $this->out(
                    "Catégorie « " . ucfirst($cat) . " » : {$count} article(s), de {$min} à {$max}. {$enStock} en stock.\nExemples : {$exemples}.",
                    ['Voir les catégories', 'Comment acheter ?'],
                    ['label' => 'Voir ' . $cat, 'url' => route('products.category', $cat)]
                );
            }
        }

        // 2. La roue
        if ($this->has($text, ['spin', 'roue', 'tirage', 'tourner', 'gagner'])) {
            $reply = "La roue : dès 2 articles dans ton panier, un tour t'est offert avant de valider ton achat. Tu peux gagner un coupon (-10 %, -25 %, -50 %, et très rarement -100 %), un tirage supplémentaire, ou « Oups » (pas de gain).";
            $link = null;

            if (auth()->check()) {
                $n = Spin::where('user_id', auth()->id())->value('available_spins') ?? 0;
                $reply .= "\nTu as actuellement {$n} spin(s) disponible(s).";
                $link = ['label' => 'Aller à la roue', 'url' => route('spin.spin')];
            }

            return $this->out($reply, ['Comment utiliser un coupon ?', 'Voir les catégories'], $link);
        }

        // 3. Les coupons
        if ($this->has($text, ['coupon', 'reduction', 'code promo', 'promo', 'remise'])) {
            $reply = "Un coupon gagné à la roue s'applique dans ton panier : clique sur ton coupon ou saisis son code (ex. SPIN-AB12CD), puis valide ton achat. Chaque coupon ne sert qu'une seule fois.";
            $link = null;

            if (auth()->check()) {
                $coupons = Coupon::where('user_id', auth()->id())->where('used', false)->get();
                if ($coupons->isNotEmpty()) {
                    $liste = $coupons->map(fn ($c) => $c->code . ' (-' . $c->discount_percent . ' %)')->implode(', ');
                    $reply .= "\nTes coupons disponibles : {$liste}.";
                } else {
                    $reply .= "\nTu n'as aucun coupon disponible pour le moment.";
                }
                $link = ['label' => 'Ouvrir mon panier', 'url' => route('cart.index')];
            } else {
                $reply .= "\nConnecte-toi pour voir tes coupons.";
            }

            return $this->out($reply, ['Comment marche la roue ?', 'Voir les catégories'], $link);
        }

        // 4. Le panier
        if ($this->has($text, ['panier'])) {
            if (!auth()->check()) {
                return $this->out("Connecte-toi pour utiliser le panier.", $menu);
            }

            $cart = session('cart', []);
            if (empty($cart)) {
                $lines = \App\Support\Cart::lines();
                if (empty($lines)) {
                    return $this->out("Ton panier est vide.", ['Voir les catégories'], ['label' => 'Voir le catalogue', 'url' => route('products.index')]);
                }

                $products = Product::whereIn('id', collect($lines)->pluck('product_id'))->get()->keyBy('id');
                $total = collect($lines)->sum(function ($l) use ($products) {
                    $p = $products->get($l['product_id']);
                    return $p ? \App\Support\Cart::unitPrice($p, $l['option']) * $l['qty'] : 0;
                });
                $nb = \App\Support\Cart::count();
            }

            $products = Product::whereIn('id', array_keys($cart))->get();
            $total = $products->sum(fn ($p) => $p->price * $cart[$p->id]);
            $nb = array_sum($cart);

            return $this->out(
                "Ton panier contient {$nb} article(s), pour un total de " . $this->ar($total) . ".",
                ['Comment marche la roue ?', 'Comment utiliser un coupon ?'],
                ['label' => 'Ouvrir mon panier', 'url' => route('cart.index')]
            );
        }

        // 5. Comment acheter
        if ($this->has($text, ['comment acheter', 'comment commander', 'passer commande', 'passer une commande', 'acheter'])) {
            return $this->out(
                "Pour acheter : 1) connecte-toi, 2) ajoute des articles au panier, 3) clique sur « Valider l'achat ». À partir de 2 articles, tu passes d'abord par la roue, puis tu reviens valider (avec ton éventuel coupon).",
                ['Comment marche la roue ?', 'Voir les catégories']
            );
        }

        // 6. Les commandes de l'utilisateur
        if ($this->has($text, ['commande', 'historique', 'achats'])) {
            if (!auth()->check()) {
                return $this->out("Connecte-toi pour voir tes commandes.", $menu);
            }

            $orders = Order::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(3)->get();
            if ($orders->isEmpty()) {
                return $this->out("Tu n'as pas encore passé de commande.", ['Voir les catégories']);
            }

            $liste = $orders->map(fn ($o) => "#{$o->id} : {$o->nb_articles} article(s), " . $this->ar($o->total) . ', le ' . $o->created_at->format('d/m/Y'))->implode("\n");

            return $this->out(
                "Tes dernières commandes :\n{$liste}",
                $menu,
                ['label' => 'Mon profil', 'url' => route('profile.edit')]
            );
        }

        // 7. Paiement (adapte ce texte quand le paiement sera actif)
        if ($this->has($text, ['paiement', 'payer', 'mvola', 'orange money', 'airtel'])) {
            return $this->out("Le paiement en ligne par MVola est en cours d'intégration. Pour l'instant, la validation de l'achat enregistre ta commande.", $menu);
        }

        // 8. Livraison (à compléter avec tes vraies informations)
        if ($this->has($text, ['livraison', 'livrer', 'expedition', 'delai', 'retrait'])) {
            return $this->out("Les modalités de livraison ne sont pas encore renseignées sur le site. Contacte la boutique pour plus de détails.", $menu);
        }

        // 9. Prix en général
        if ($this->has($text, ['prix', 'combien', 'tarif', 'cher', 'cout'])) {
            $min = $this->ar(Product::min('price'));
            $max = $this->ar(Product::max('price'));

            return $this->out("Nos articles vont de {$min} à {$max}. Dis-moi une catégorie (sac, survêtement...) pour avoir les prix détaillés.", ['Voir les catégories']);
        }

        // 10. Stock en général
        if ($this->has($text, ['stock', 'disponible', 'rupture'])) {
            $dispo = Product::where('stock', '>', 0)->count();
            $rupture = Product::where('stock', '<=', 0)->count();

            return $this->out("{$dispo} article(s) sont en stock et {$rupture} en rupture. Précise une catégorie pour le détail.", ['Voir les catégories']);
        }

        // 11. Catalogue et catégories
        if ($this->has($text, ['catalogue', 'categorie', 'produit', 'article', 'vendez', 'vendre', 'boutique'])) {
            $liste = Product::select('category', DB::raw('count(*) as n'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->get()
                ->map(fn ($c) => ucfirst($c->category) . " ({$c->n})")
                ->implode(', ');

            return $this->out(
                "Nos catégories : {$liste}.",
                ['Prix des sacs', 'Comment acheter ?'],
                ['label' => 'Voir le catalogue', 'url' => route('products.index')]
            );
        }

        // 12. Aide
        if ($this->has($text, ['aide', 'help', 'que peux', 'que sais', 'tu peux', 'vous pouvez'])) {
            return $this->out(
                "Je peux te renseigner sur nos catégories et leurs prix, le stock, le fonctionnement de la roue, tes coupons, ton panier et tes commandes.",
                $menu
            );
        }

        // 13. Salutations
        if ($this->has($text, ['bonjour', 'salut', 'bonsoir', 'hello', 'coucou'])) {
            $nom = auth()->check() ? ' ' . auth()->user()->name : '';

            return $this->out("Bonjour{$nom} ! Je suis l'assistant Équip'Style. Comment puis-je t'aider ?", $menu);
        }

        // 14. Remerciements et au revoir
        if ($this->has($text, ['merci'])) {
            return $this->out("Avec plaisir ! Autre chose ?", $menu);
        }
        if ($this->has($text, ['au revoir', 'bye', 'a bientot'])) {
            return $this->out("À bientôt sur Équip'Style !");
        }

        // 15. Question non comprise
        return $this->out(
            "Je n'ai pas bien compris. Tu peux me poser une question sur nos articles, les prix, la roue, tes coupons ou tes commandes.",
            $menu
        );
    }
}