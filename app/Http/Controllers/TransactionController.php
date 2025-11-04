<?php
namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Transaction;
use App\Models\Client;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/transactions/compte/{compteId}",
     *     summary="Lister les transactions d'un compte",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string")
     *     ),
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
    public function listByCompte($compteId)
    {
        $transactions = Transaction::where('compte_id', $compteId)->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/depot",
     *     summary="Effectuer un dépôt sur un compte",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compteId","montant"},
     *             @OA\Property(property="compteId", type="string"),
     *             @OA\Property(property="montant", type="number", minimum=1000),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dépôt effectué",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function depot(Request $request)
    {
        $request->validate([
            'compteId' => 'required|exists:comptes,id',
            'montant' => 'required|numeric|min:1000',
        ]);
        $compte = Compte::findOrFail($request->compteId);
        DB::transaction(function () use ($compte, $request) {
            $compte->solde += $request->montant;
            $compte->save();
            $transaction = Transaction::create([
                'compte_id' => $compte->id,
                'type' => 'depot',
                'montant' => $request->montant,
                'description' => $request->description ?? 'Dépôt',
            ]);
            NotificationService::notify($compte->client, 'Dépôt de ' . $request->montant . ' FCFA effectué.');
        });
        return response()->json(['success' => true, 'message' => 'Dépôt effectué']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/retrait",
     *     summary="Effectuer un retrait sur un compte",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compteId","montant"},
     *             @OA\Property(property="compteId", type="string"),
     *             @OA\Property(property="montant", type="number", minimum=1000),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retrait effectué",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function retrait(Request $request)
    {
        $request->validate([
            'compteId' => 'required|exists:comptes,id',
            'montant' => 'required|numeric|min:1000',
        ]);
        $compte = Compte::findOrFail($request->compteId);
        if ($compte->solde < $request->montant) {
            return response()->json(['success' => false, 'message' => 'Solde insuffisant'], 400);
        }
        DB::transaction(function () use ($compte, $request) {
            $compte->solde -= $request->montant;
            $compte->save();
            $transaction = Transaction::create([
                'compte_id' => $compte->id,
                'type' => 'retrait',
                'montant' => $request->montant,
                'description' => $request->description ?? 'Retrait',
            ]);
            NotificationService::notify($compte->client, 'Retrait de ' . $request->montant . ' FCFA effectué.');
        });
        return response()->json(['success' => true, 'message' => 'Retrait effectué']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/archiver-semaine",
     *     summary="Archiver les transactions de la semaine dans MongoDB",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Transactions archivées",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function archiverTransactionsSemaine()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $transactions = Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
        if ($transactions->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Aucune transaction à archiver.']);
        }
        $archive = new \App\Models\ArchiveTransaction();
        $archive->setWeeklyCollection($startOfWeek->toDateString());
        foreach ($transactions as $t) {
            $archive->create($t->toArray());
        }
        // Optionnel : supprimer les transactions archivées de Postgres
        // Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])->delete();
        return response()->json(['success' => true, 'message' => 'Transactions archivées dans MongoDB.']);
    }
}
