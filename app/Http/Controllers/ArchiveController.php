<?php
namespace App\Http\Controllers;

use App\Models\ArchiveTransaction;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/archives/semaine/{semaineId}",
     *     summary="Lister les transactions archivées d'une semaine",
     *     tags={"Archives"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="semaineId",
     *         in="path",
     *         required=true,
     *         description="Identifiant de la semaine (ex: 2025-44)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions archivées",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function listBySemaine($semaineId)
    {
        $collection = 'transactions_semaine_' . $semaineId;
        $transactions = (new ArchiveTransaction)->setCollection($collection)->get();
        return response()->json(['success' => true, 'data' => $transactions]);
    }
}
