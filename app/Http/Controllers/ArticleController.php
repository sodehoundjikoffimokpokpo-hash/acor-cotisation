<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Client;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    /**
     * Affiche la liste des articles.
     */
    public function index()
    {
        $articles = Article::all();
        return view('admin.articles', compact('articles'));
    }

    /**
     * Enregistre un nouvel article.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'purchase_price' => 'nullable|numeric',
            'transport_fees' => 'nullable|numeric',
            'search_fees' => 'nullable|numeric',
            'collector_commission' => 'nullable|numeric',
            'admin_fees' => 'nullable|numeric',
            'price_provision' => 'nullable|numeric',
            'acor_margin' => 'nullable|numeric',
        ]);

        Article::create($request->all());
        return back()->with('success', 'Article ajouté avec succès !');
    }

    /**
     * Ajoute du stock à un article existant.
     */


    /**
     * Valider la livraison d'un article à un client et décrémenter le stock.
     */
    public function deliverArticle(Request $request, $clientId)
    {
        // On récupère le client avec son article associé
        $client = Client::with('article')->findOrFail($clientId);

        // 1. Sécurité : Vérifier s'il est bien au statut "Prêt pour livraison"
        if ($client->status !== 'goal_reached') {
            return back()->with('error', 'Ce client n\'a pas encore atteint son objectif de cotisation.');
        }

        // 2. Sécurité : Vérifier si l'article est assigné et s'il y a assez de stock
        if (!$client->article || $client->article->stock_quantity < 1) {
            return back()->with('error', 'Le stock est insuffisant pour cet article (' . ($client->article ? $client->article->name : 'Aucun article assigné') . ').');
        }

        // 3. Transaction de base de données
        DB::transaction(function () use ($client, $request) {
            // A. Décrémenter le stock de l'article de 1
            $client->article->decrement('stock_quantity', 1);

            // B. Mettre à jour le statut du client à "completed" (Livré)
            $client->update([
                'status'       => 'completed',
                'delivered_at' => now(),
            ]);

            // C. Traçabilité dans l'Audit Log
            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'DELIVER_ARTICLE',
                'description' => "Remise de l'article '{$client->article->name}' au client {$client->firstname} {$client->name}. Stock restant : {$client->article->stock_quantity}.",
                'ip_address'  => $request->ip(),
            ]);
        });

        return back()->with('success', "L'article a été livré au client avec succès ! Le stock a été mis à jour automatiquement.");
    }

    public function updateStock(Request $request, $id)
{
    // On valide que 'quantity' est bien présent
    $request->validate([
        'quantity' => 'required|integer|min:1'
    ]);

    $article = Article::findOrFail($id);
    $article->increment('stock_quantity', $request->quantity);

    return back()->with('success', 'Stock mis à jour !');
}
}
