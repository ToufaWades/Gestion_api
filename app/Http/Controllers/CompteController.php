<?php

/**
 * @OA\Info(
 *     title="Gestion des Comptes API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes, incluant toutes les opérations CRUD, archivage, blocage et déblocage.",
 *     contact={
 *         "email": "support@gestionapi.com"
 *     }
 * )
 */
namespace App\Http\Controllers;

use App\Models\Compte;
use Illuminate\Http\Request;
use App\Traits\ApiQueryTrait;
use Illuminate\Support\Carbon;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompteFilterRequest;
use App\Http\Requests\BlocageCompteRequest;
use App\Services\CompteLookupService;
use App\Http\Resources\CompteResource;
use App\Exceptions\CompteNotFoundException;

class CompteController extends Controller
{
    use ApiResponseTrait, ApiQueryTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Liste tous les comptes non archivés",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="order", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste paginée des comptes")
     * )
     */
    public function index(CompteFilterRequest $request)
    {
        // Filtrer pour exclure les comptes bloqués ou archivés
        $query = Compte::query()
            ->where('statut_compte', '!=', 'bloqué')
            ->where(function($q) {
                $q->whereNull('archived')->orWhere('archived', false);
            });
        $comptes = $this->applyQueryFilters($query, $request);
        $pagination = [
            'currentPage' => $comptes->currentPage(),
            'itemsPerPage' => $comptes->perPage(),
            'totalItems' => $comptes->total(),
            'totalPages' => $comptes->lastPage(),
        ];
        return $this->paginatedResponse($comptes->items(), $pagination, 'Liste récupérée avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comptes/{id}",
     *     summary="Met à jour un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="solde", type="number"),
     *             @OA\Property(property="type_compte", type="string"),
     *             @OA\Property(property="statut_compte", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Compte mis à jour")
     * )
     */
    public function update(Request $request, $id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        $compte->fill($request->all());
        $compte->save();
        return $this->successResponse($compte, 'Compte mis à jour');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{id}",
     *     summary="Supprime un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Compte supprimé")
     * )
     */
    public function destroy($id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        $compte->delete();
        return $this->successResponse(null, 'Compte supprimé');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/desarchive",
     *     summary="Désarchive un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Compte désarchivé")
     * )
     */
    public function desarchive($id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        $compte->archived = false;
        $compte->save();
        return $this->successResponse($compte, 'Compte désarchivé avec succès');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/debloquer",
     *     summary="Débloque un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Compte débloqué")
     * )
     */
    public function debloquer($id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        $compte->statut_compte = 'actif';
        $compte->date_debut_blocage = null;
        $compte->date_fin_blocage = null;
        $compte->motif_blocage = null;
        $compte->save();
        return $this->successResponse($compte, 'Compte débloqué avec succès');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/mes-comptes",
     *     summary="Liste les comptes du client connecté",
     *     tags={"Comptes"},
     *     @OA\Response(response=200, description="Liste des comptes du client")
     * )
     */
    public function mesComptes(Request $request)
    {
        $telephone = $request->user()->telephone ?? null;
        if (!$telephone) {
            return $this->errorResponse('Téléphone utilisateur manquant', 400);
        }
        $comptes = Compte::client($telephone)->get();
        return $this->successResponse($comptes, 'Comptes du client récupérés');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{numero}",
     *     summary="Détail d’un compte par numéro",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="numero", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détail du compte")
     * )
     */
    public function show($numero)
    {
        $compte = Compte::numero($numero)->first();
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        return $this->successResponse($compte, 'Détail du compte récupéré');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/archive",
     *     summary="Archive un compte au lieu de le supprimer",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Compte archivé")
     * )
     */
    public function archive($id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        $compte->archived = true;
        $compte->save();
        return $this->successResponse($compte, 'Compte archivé avec succès');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{compte}/bloquer",
     *     summary="Bloquer un compte épargne (enregistre la période et le motif)",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="compte", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="date_debut_blocage", type="string", format="date"),
     *             @OA\Property(property="date_fin_blocage", type="string", format="date"),
     *             @OA\Property(property="motif_blocage", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Compte bloqué")
     * )
     */
    public function bloquer(BlocageCompteRequest $request, $compteIdentifier)
    {
        $compte = null;
        if (is_numeric($compteIdentifier)) {
            $compte = Compte::find($compteIdentifier);
        }

        if (!$compte) {
            $compte = Compte::where('numero_compte', $compteIdentifier)->first();
        }

        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }

        // Ne bloquer que les comptes épargne
        if ($compte->type !== 'epargne') {
            return $this->errorResponse('Seuls les comptes épargne peuvent être bloqués', 400);
        }

        $compte->date_debut_blocage = $request->input('date_debut_blocage');
        $compte->date_fin_blocage = $request->input('date_fin_blocage');
        $compte->motif_blocage = $request->input('motif_blocage');

        try {
            $start = Carbon::parse($compte->date_debut_blocage)->startOfDay();
            $end = Carbon::parse($compte->date_fin_blocage)->endOfDay();
            if (Carbon::now()->between($start, $end)) {
                $compte->statut_compte = 'bloqué';
                $compte->transactions()->update(['archived' => true]);
                Log::info('Compte bloqué immédiatement via endpoint', ['compte_id' => $compte->id]);
            }
        } catch (\Exception $e) {
        }

        $compte->save();

        return $this->successResponse($compte, 'Données de blocage enregistrées');
    }


    /**
     * @OA\Post(
     *     path="/api/v1/comptes/numero/{numero}/bloquer",
     *     summary="Bloquer un compte épargne par numéro",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="numero", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="date_debut_blocage", type="string", format="date"),
     *             @OA\Property(property="date_fin_blocage", type="string", format="date"),
     *             @OA\Property(property="motif_blocage", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Compte bloqué")
     * )
     */
    public function bloquerByNumero(BlocageCompteRequest $request, $numero)
    {
        return $this->bloquer($request, $numero);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{numeroCompte}",
     *     summary="Récupère un compte par son numéro (Admin ou Client)",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="numeroCompte", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Compte récupéré")
     * )
     */
    public function showByNumero($numeroCompte, CompteLookupService $service)
    {
        $compte = $service->findByNumero($numeroCompte);

        if (! $compte) {
            throw new CompteNotFoundException($numeroCompte);
        }

        $resource = new CompteResource($compte);
        return $this->successResponse($resource->toArray(request()), 'Détail du compte récupéré');
    }
}
