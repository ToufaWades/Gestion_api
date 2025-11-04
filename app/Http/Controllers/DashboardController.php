<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/global",
     *     summary="Dashboard global (admin)",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques globales",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function globalDashboard()
    {
        $totalComptes = Compte::count();
        $soldeTotal = Compte::sum('solde');
        $totalTransactions = Transaction::count();
        $lastTransactions = Transaction::orderByDesc('created_at')->limit(10)->get();
        $comptesToday = Compte::whereDate('created_at', today())->get();
        return response()->json([
            'success' => true,
            'data' => [
                'totalComptes' => $totalComptes,
                'soldeTotal' => $soldeTotal,
                'totalTransactions' => $totalTransactions,
                'lastTransactions' => $lastTransactions,
                'comptesToday' => $comptesToday,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/client/{clientId}",
     *     summary="Dashboard d'un client",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="clientId",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques du client",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function clientDashboard($clientId)
    {
        $comptes = Compte::where('client_id', $clientId)->get();
        $totalComptes = $comptes->count();
        $soldeTotal = $comptes->sum('solde');
        $transactions = Transaction::whereIn('compte_id', $comptes->pluck('id'))->orderByDesc('created_at')->get();
        $totalTransactions = $transactions->count();
        $lastTransactions = $transactions->take(10);
        return response()->json([
            'success' => true,
            'data' => [
                'totalComptes' => $totalComptes,
                'soldeTotal' => $soldeTotal,
                'totalTransactions' => $totalTransactions,
                'lastTransactions' => $lastTransactions,
                'comptes' => $comptes,
            ]
        ]);
    }

    // Liste toutes les transactions (admin)
    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     summary="Lister toutes les transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function listAllTransactions()
    {
        $transactions = Transaction::orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/{id}",
     *     summary="Récupérer une transaction par id",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la transaction",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction trouvée",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        return response()->json(['success' => true, 'data' => $transaction]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/transactions/{id}",
     *     summary="Annuler/supprimer une transaction (admin)",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la transaction",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction supprimée",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function deleteTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        return response()->json(['success' => true, 'message' => 'Transaction supprimée']);
    }
}
