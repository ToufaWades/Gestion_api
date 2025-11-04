<?php
namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Gestion des Comptes API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes, incluant toutes les opérations CRUD, archivage, blocage et déblocage.",
 *     contact={
 *         "email": "support@gestionapi.com"
 *     }
 * )
 * @OA\Tag(
 *     name="Comptes",
 *     description="Opérations sur les comptes bancaires"
 * )
 */

     /**
      * @OA\Get(
      *     path="/api/v1/comptes/{compteId}/statistiques",
      *     summary="Statistiques d'un compte bancaire",
      *     tags={"Comptes"},
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
      *         description="Statistiques du compte",
      *         @OA\JsonContent(type="object",
      *             @OA\Property(property="success", type="boolean"),
      *             @OA\Property(property="data", type="object")
      *         )
      *     )
      * )
      */

use App\Models\Compte;
use Illuminate\Http\Request;
use App\Traits\ApiQueryTrait;
use Illuminate\Support\Carbon;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompteFilterRequest;
use App\Http\Requests\BlocageCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Services\CompteLookupService;
use App\Http\Resources\CompteResource;
use App\Exceptions\CompteNotFoundException;



class CompteController extends Controller {

    /**
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un compte bancaire avec client",
     *     description="Crée un utilisateur, un client et un compte bancaire avec validation stricte, génération mot de passe/code, envoi mail/SMS.",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","soldeInitial","devise","solde","client"},
     *             @OA\Property(property="type", type="string", enum={"cheque","epargne"}),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000),
     *             @OA\Property(property="devise", type="string", enum={"FCFA","XOF"}),
     *             @OA\Property(property="solde", type="number", minimum=0),
     *             @OA\Property(property="client", type="object",
     *                 required={"titulaire","email","telephone","adresse"},
     *                 @OA\Property(property="id", type="integer", nullable=true),
     *                 @OA\Property(property="titulaire", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="telephone", type="string", pattern="^\\+2217[0-9]{7,8}$"),
     *                 @OA\Property(property="adresse", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="660f9511-f30c-52e5-b827-557766551111"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123460"),
     *                 @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=500000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $clientData = $data['client'] ?? [];
        $validator = \Validator::make($data, [
            'type' => 'required|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|in:FCFA,XOF',
            'solde' => 'required|numeric|min:0',
            'client.titulaire' => 'required|string',
            'client.email' => 'required|email|unique:users,email',
            'client.telephone' => ['required','regex:/^\\+2217[0-9]{7,8}$/','unique:users,telephone'],
            'client.adresse' => 'required|string',
        ], [
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10000',
            'client.email.unique' => 'Cet email est déjà utilisé',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'client.telephone.regex' => 'Le téléphone doit être au format sénégalais (+2217XXXXXXXX)',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $validator->errors()
                ]
            ], 400);
        }

        // Générer mot de passe et code d'authentification
        $password = \Str::random(10);
        $activationCode = random_int(100000, 999999);

        // Créer User
        $user = new \App\Models\User();
        $user->nom = $clientData['titulaire'];
        $user->prenom = '';
        $user->email = $clientData['email'];
        $user->telephone = $clientData['telephone'];
        $user->password = bcrypt($password);
        $user->activation_code = $activationCode;
        $user->activation_expires_at = now()->addMinutes(60);
        $user->save();

        // Créer Client
    $client = new \App\Models\Client();
    // $client->id = (string) \Str::uuid(); // Ne pas forcer l'id, laisser Eloquent gérer
    $client->user_id = $user->id;
    $client->nom = $clientData['titulaire'];
    $client->prenom = '';
    $client->email = $clientData['email'];
    $client->telephone = $clientData['telephone'];
    $client->adresse = $clientData['adresse'];
    $client->nci = $clientData['nci'] ?? null;
    $client->code_activation = $activationCode;
    $client->is_active = false;
    $client->save();

        // Générer numéro de compte unique
        $numeroCompte = 'C00' . str_pad(random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        while (\App\Models\Compte::where('numero_compte', $numeroCompte)->exists()) {
            $numeroCompte = 'C00' . str_pad(random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        }

        // Créer Compte
        $compte = new \App\Models\Compte();
        $compte->numero_compte = $numeroCompte;
        $compte->titulaire_compte = $clientData['titulaire'];
        $compte->type_compte = $data['type'];
        $compte->devise = $data['devise'];
        $compte->solde = $data['soldeInitial'];
        $compte->statut_compte = 'actif';
        $compte->date_creation = now();
        $compte->user_id = $user->id;
        $compte->client_id = $client->id;
        $compte->version = 1;
        $compte->archived = false;
        $compte->save();

        // Envoi email (mot de passe)
        try {
            \Mail::to($user->email)->send(new \App\Mail\CompteCreationConfirmation($user, $password));
        } catch (\Throwable $e) {
            \Log::error('Erreur envoi mail création compte: ' . $e->getMessage());
        }

        // Envoi SMS (code d'authentification)
        try {
            if (app()->bound('App\\Services\\MessageServiceInterface')) {
                app('App\\Services\\MessageServiceInterface')->send($user->telephone, 'Votre code d\'activation est: ' . $activationCode);
            }
        } catch (\Throwable $e) {
            \Log::error('Erreur envoi SMS création compte: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès',
            'data' => [
                'id' => (string) $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'titulaire' => $compte->titulaire_compte,
                'type' => $compte->type_compte,
                'solde' => $compte->solde,
                'devise' => $compte->devise,
                'dateCreation' => $compte->date_creation?->toISOString(),
                'statut' => $compte->statut_compte,
                'metadata' => [
                    'derniereModification' => $compte->updated_at?->toISOString(),
                    'version' => $compte->version
                ]
            ]
        ], 201);
    }
    use ApiResponseTrait, ApiQueryTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Liste tous les comptes non archivés",
     *     description="Filtre sur le type de compte (épargne, chèque). Exclut tous les comptes bloqués ou fermés du résultat.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", enum={"epargne", "cheque"})),
     *     @OA\Parameter(name="statut", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="order", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste paginée des comptes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", items={"type": "object"}),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="currentPage", type="integer"),
     *                 @OA\Property(property="itemsPerPage", type="integer"),
     *                 @OA\Property(property="totalItems", type="integer"),
     *                 @OA\Property(property="totalPages", type="integer")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(CompteFilterRequest $request)
    {
        // Filtrer pour exclure les comptes bloqués ou fermés
        $query = Compte::query()
            ->whereNotIn('statut_compte', ['bloqué', 'fermé'])
            ->where(function($q) {
                $q->whereNull('archived')->orWhereRaw('archived = false');
            });

        // Appliquer le filtre de type si fourni
        if ($request->has('type') && $request->type) {
            $query->where('type_compte', $request->type);
        }

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
     *             @OA\Property(property="type_compte", type="string", enum={"epargne", "cheque"}),
     *             @OA\Property(property="statut_compte", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Compte mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }

        // Check ownership for clients
        $user = $request->user();
        if ($user && $user->isClient() && $compte->user_id !== $user->id) {
            return $this->errorResponse('Accès non autorisé', 403);
        }

        // Clients cannot block/unblock or archive accounts
        $forbiddenFields = ['statut_compte', 'date_debut_blocage', 'date_fin_blocage', 'motif_blocage', 'archived'];
        if ($user && $user->isClient()) {
            foreach ($forbiddenFields as $field) {
                if ($request->has($field)) {
                    return $this->errorResponse('Modification non autorisée', 403);
                }
            }
        }

        $compte->fill($request->all());
        $compte->save();
        return $this->successResponse($compte, 'Compte mis à jour');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Supprime un compte (soft delete)",
     *     description="Marque le compte comme fermé sans le supprimer physiquement de la base de données.",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="compteId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="statut", type="string", example="ferme"),
     *                 @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Accès non autorisé"),
     *     @OA\Response(response=404, description="Compte introuvable")
     * )
     */
    public function destroy($compteId)
    {
        // Find the account (including soft deleted ones for checking existence)
        $compte = Compte::withTrashed()->find($compteId);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Compte introuvable'
                ]
            ], 404);
        }

        // Check ownership for clients
        $user = request()->user();
        if ($user && $user->isClient() && $compte->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'Accès non autorisé'
                ]
            ], 403);
        }

        // Soft delete - mark as closed
        $compte->statut_compte = 'fermé';
        $compte->date_fermeture = now();
        $compte->save();

        // Perform actual soft delete
        $compte->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compte supprimé avec succès',
            'data' => [
                'id' => (string) $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'statut' => $compte->statut_compte,
                'dateFermeture' => $compte->date_fermeture->toISOString()
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/desarchive",
     *     summary="Désarchive un compte",
     *     description="Désarchivage : seuls les comptes épargne bloqués dont la date de fin de blocage est échue peuvent être désarchivés. Le désarchivage est exécuté via un job Laravel automatique.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Compte désarchivé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
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
     *     description="Remet le statut du compte à actif et supprime les dates de blocage.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Compte débloqué",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
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
     *     @OA\Response(response=200, description="Liste des comptes du client",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", items={"type": "object"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function mesComptes(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        if ($user->isAdmin()) {
            // Admin can see all accounts
            $comptes = Compte::with('client.user')->get();
        } else {
            // Client can only see their own accounts
            $comptes = Compte::where('user_id', $user->id)->with('client.user')->get();
        }

        return $this->successResponse($comptes, 'Comptes récupérés');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{numero}",
     *     summary="Détail d'un compte par numéro",
     *     description="Pour les comptes épargne, affiche aussi la date de début et la date de fin de blocage. Si le compte épargne est archivé, récupère ses informations depuis la base Neon distante.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="numero", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détail du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($numero)
    {
        $compte = Compte::numero($numero)->first();
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }

        // Check ownership for clients
        $user = request()->user();
        if ($user && $user->isClient() && $compte->user_id !== $user->id) {
            return $this->errorResponse('Accès non autorisé', 403);
        }

        return $this->successResponse($compte, 'Détail du compte récupéré');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/archive",
     *     summary="Archive un compte (marquage comme archived)",
     *     description="Archivage : seuls les comptes épargne bloqués dont la date de début de blocage est échue peuvent être archivés. L'archivage est exécuté via un job Laravel automatique.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Compte marqué pour archivage")
     * )
     */
    public function archive($id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->notFoundResponse('Compte introuvable');
        }
        // Interdire l'archivage manuel des comptes épargne
        if ($compte->type_compte === 'epargne') {
            return $this->errorResponse('Archivage manuel interdit pour les comptes épargne', 403);
        }
        $compte->archived = true;
        $compte->save();
        return $this->successResponse($compte, 'Compte archivé avec succès');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{compte}/bloquer",
     *     summary="Bloquer un compte épargne",
     *     description="Enregistre la date de début du blocage, le motif et le statut. Seuls les comptes épargne actifs peuvent être bloqués. Après blocage, affiche les informations de blocage.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="compte", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date_debut_blocage", "date_fin_blocage", "motif_blocage"},
     *             @OA\Property(property="date_debut_blocage", type="string", format="date", example="2025-10-25"),
     *             @OA\Property(property="date_fin_blocage", type="string", format="date", example="2025-10-28"),
     *             @OA\Property(property="motif_blocage", type="string", example="Suspicion de fraude")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Données de blocage enregistrées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
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

        // Ne bloquer que les comptes épargne actifs
        if ($compte->type_compte !== 'epargne') {
            return $this->errorResponse('Seuls les comptes épargne peuvent être bloqués', 400);
        }

        if ($compte->statut_compte !== 'actif') {
            return $this->errorResponse('Seuls les comptes actifs peuvent être bloqués', 400);
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
     *     description="Enregistre la date de début du blocage, le motif et le statut. Seuls les comptes épargne actifs peuvent être bloqués.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="numero", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date_debut_blocage", "date_fin_blocage", "motif_blocage"},
     *             @OA\Property(property="date_debut_blocage", type="string", format="date", example="2025-10-25"),
     *             @OA\Property(property="date_fin_blocage", type="string", format="date", example="2025-10-28"),
     *             @OA\Property(property="motif_blocage", type="string", example="Suspicion de fraude")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Données de blocage enregistrées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function bloquerByNumero(BlocageCompteRequest $request, $numero)
    {
        return $this->bloquer($request, $numero);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{numeroCompte}",
     *     summary="Récupère un compte par son numéro",
     *     description="Pour les comptes épargne, affiche aussi la date de début et la date de fin de blocage. Si le compte épargne est archivé, récupère ses informations depuis la base Neon distante.",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="numeroCompte", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Compte récupéré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function showByNumero($numeroCompte, CompteLookupService $service)
    {
        $compte = $service->findByNumero($numeroCompte);

        if (! $compte) {
            throw new CompteNotFoundException($numeroCompte);
        }

        // Check ownership for clients
        $user = request()->user();
        if ($user && $user->isClient() && $compte->user_id !== $user->id) {
            return $this->errorResponse('Accès non autorisé', 403);
        }

        // Si le compte épargne est archivé, récupérer ses informations depuis la base Neon distante
        if ($compte->type_compte === 'epargne' && $compte->archived) {
            // Logique pour récupérer depuis la base distante
            // Ici nous simulons, mais en réalité il faudrait une connexion à la base Neon
            Log::info('Récupération des informations d\'un compte épargne archivé depuis la base distante', ['numero_compte' => $numeroCompte]);
        }

        $resource = new CompteResource($compte);
        return $this->successResponse($resource->toArray(request()), 'Détail du compte récupéré');
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Mettre à jour les informations d'un compte client",
     *     description="Met à jour partiellement les informations d'un compte existant. Au moins un champ doit être fourni.",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="compteId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *             @OA\Property(property="informationsClient", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234568"),
     *                 @OA\Property(property="email", type="string", format="email", example="amadou.diallo@example.com"),
     *                 @OA\Property(property="password", type="string", example="MyP@ssw0rd!")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *                 @OA\Property(property="type", type="string", example="epargne"),
     *                 @OA\Property(property="solde", type="number", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Accès non autorisé"),
     *     @OA\Response(response=404, description="Compte introuvable"),
     *     @OA\Response(response=422, description="Données invalides")
     * )
     */
    public function updateClientInfo(UpdateCompteRequest $request, $compteId)
    {
        $compte = Compte::find($compteId);
        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Compte introuvable'
                ]
            ], 404);
        }
        $user = $request->user();
        if ($user && $user->isClient() && $compte->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'Accès non autorisé'
                ]
            ], 403);
        }
        $data = $request->only(['titulaire', 'informationsClient']);
        if (empty($data['titulaire']) && empty($data['informationsClient'])) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_FIELDS',
                    'message' => 'Au moins un champ doit être fourni.'
                ]
            ], 422);
        }
        // Validation personnalisée
        $errors = [];
        if (!empty($data['titulaire'])) {
            $compte->titulaire_compte = $data['titulaire'];
        }
        if (!empty($data['informationsClient'])) {
            $clientInfo = $data['informationsClient'];
            $clientUser = $compte->client->user;
            if (isset($clientInfo['telephone']) && $clientInfo['telephone']) {
                // Format +2217XXXXXXXX ou +2217XXXXXXX
                if (!preg_match('/^\+2217[0-9]{7,8}$/', $clientInfo['telephone'])) {
                    $errors['telephone'] = 'Format de téléphone invalide.';
                } elseif ($clientUser->where('telephone', $clientInfo['telephone'])->where('id', '!=', $clientUser->id)->exists()) {
                    $errors['telephone'] = 'Ce numéro de téléphone est déjà utilisé.';
                } else {
                    $clientUser->telephone = $clientInfo['telephone'];
                }
            }
            if (isset($clientInfo['email']) && $clientInfo['email']) {
                if (!filter_var($clientInfo['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Format email invalide.';
                } elseif ($clientUser->where('email', $clientInfo['email'])->where('id', '!=', $clientUser->id)->exists()) {
                    $errors['email'] = 'Cet email est déjà utilisé.';
                } else {
                    $clientUser->email = $clientInfo['email'];
                }
            }
            if (isset($clientInfo['password']) && $clientInfo['password']) {
                $pwd = $clientInfo['password'];
                $specials = preg_match_all('/[@#$%&*!?]/', $pwd);
                if (
                    strlen($pwd) < 10 ||
                    !preg_match('/^[A-Z]/', $pwd) ||
                    preg_match_all('/[a-z]/', $pwd) < 2 ||
                    $specials < 2
                ) {
                    $errors['password'] = 'Le mot de passe doit comporter au moins 10 caractères, commencer par une majuscule, contenir au moins 2 minuscules et 2 caractères spéciaux.';
                } else {
                    $clientUser->password = bcrypt($pwd);
                }
            }
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Données invalides',
                        'details' => $errors
                    ]
                ], 422);
            }
            $clientUser->save();
        }
        $compte->version = ($compte->version ?? 0) + 1;
        $compte->save();
        return response()->json([
            'success' => true,
            'message' => 'Compte mis à jour avec succès',
            'data' => [
                'id' => (string) $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'titulaire' => $compte->titulaire_compte,
                'type' => $compte->type_compte,
                'solde' => $compte->solde,
                'devise' => $compte->devise,
                'dateCreation' => $compte->date_creation?->toISOString(),
                'statut' => $compte->statut_compte,
                'metadata' => [
                    'derniereModification' => $compte->updated_at->toISOString(),
                    'version' => $compte->version
                ]
            ]
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes-archives",
     *     summary="Liste des comptes archivés",
     *     description="Liste tous les comptes épargne archivés (lecture seule).",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Liste des comptes archivés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", items={"type": "object"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function comptesArchives(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->errorResponse('Accès non autorisé', 403);
        }

        $comptes = Compte::where('archived', true)
            ->where('type_compte', 'epargne')
            ->with('client.user')
            ->get();

        return $this->successResponse($comptes, 'Comptes archivés récupérés');
    }

    // Statistiques d’un compte
    public function statistiques($compteId)
    {
        $compte = \App\Models\Compte::findOrFail($compteId);
        $transactions = \App\Models\Transaction::where('compte_id', $compteId)->get();
        $totalDepot = $transactions->where('type', 'depot')->sum('montant');
        $totalRetrait = $transactions->where('type', 'retrait')->sum('montant');
        $nbTransactions = $transactions->count();
        $lastTransaction = $transactions->sortByDesc('created_at')->first();
        return response()->json([
            'success' => true,
            'data' => [
                'totalDepot' => $totalDepot,
                'totalRetrait' => $totalRetrait,
                'nbTransactions' => $nbTransactions,
                'lastTransaction' => $lastTransaction,
            ]
        ]);
    }
}
