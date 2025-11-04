<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionMongo;

class TransactionMongoController extends Controller
{
    // Enregistrer une transaction dans MongoDB
    public function store(Request $request)
    {
        $transaction = TransactionMongo::create($request->only([
            'code_transaction', 'type', 'montant', 'description', 'compte_id', 'agent_id', 'created_at'
        ]));
        return response()->json($transaction, 201);
    }

    // Lister toutes les transactions MongoDB
    public function index()
    {
        $transactions = TransactionMongo::all();
        return response()->json($transactions);
    }
}
