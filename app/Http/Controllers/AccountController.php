<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Compte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Mail\CompteCreationConfirmation;
use App\Services\MessageServiceInterface;

/**
 * @OA\Tag(
 *     name="Accounts",
 *     description="Création de comptes bancaires"
 * )
 */
class AccountController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/accounts",
     *     summary="Créer un compte bancaire avec client",
     *     description="Crée un utilisateur, un client et un compte bancaire avec validation stricte. On peut créer un compte uniquement pour un client déjà enregistré. Envoie un email et un SMS de confirmation.",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "soldeInitial", "devise", "solde", "client"},
     *             @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000, example=500000),
     *             @OA\Property(property="devise", type="string", enum={"FCFA", "XOF"}, example="FCFA"),
     *             @OA\Property(property="solde", type="number", minimum=0, example=10000),
     *             @OA\Property(property="client", type="object",
     *                 required={"id", "titulaire", "nci", "email", "telephone", "adresse"},
     *                 @OA\Property(property="id", type="integer", example=1, description="ID du client existant"),
     *                 @OA\Property(property="titulaire", type="string", example="Fatou Wade"),
     *                 @OA\Property(property="nci", type="string", pattern="^[12][0-9]{12}$", example="1234567890123"),
     *                 @OA\Property(property="email", type="string", format="email", example="fatou.wade@example.com"),
     *                 @OA\Property(property="telephone", type="string", pattern="^\\+221(77|78|70|76|75)[0-9]{7}$", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="123"),
     *                 @OA\Property(property="numeroCompte", type="string", example="ACC-20251029-1234"),
     *                 @OA\Property(property="titulaire", type="string", example="Fatou Wade"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=500000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données invalides ou client non trouvé")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string|in:FCFA,XOF',
            'solde' => 'required|numeric|min:0',
            'client' => 'required|array',
            'client.id' => 'nullable|integer|exists:clients,id',
            'client.titulaire' => 'required|string|max:255',
            'client.nci' => [
                'required',
                'string',
                'regex:/^[12][0-9]{12}$/',
            ],
            'client.email' => 'required|email',
            'client.telephone' => [
                'required',
                'string',
                'regex:/^\+221(77|78|70|76|75)[0-9]{7}$/',
            ],
            'client.adresse' => 'required|string|max:255',
        ], [
            'type.required' => 'Le type de compte est requis',
            'soldeInitial.required' => 'Le solde initial est requis',
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10000',
            'devise.required' => 'La devise est requise',
            'solde.required' => 'Le solde est requis',
            'client.required' => 'Les informations du client sont requises',
            'client.titulaire.required' => 'Le nom du titulaire est requis',
            'client.nci.regex' => 'Le NCI doit être un numéro sénégalais valide (13 chiffres commençant par 1 ou 2)',
            'client.email.required' => 'L\'email est requis',
            'client.email.unique' => 'Cet email est déjà utilisé',
            'client.telephone.required' => 'Le numéro de téléphone est requis',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'client.telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (+22177xxxxxx, etc.)',
            'client.adresse.required' => 'L\'adresse est requise',
        ]);

        // Check if client exists, if not create new client and user
        $clientExists = isset($validated['client']['id']) && $validated['client']['id'];

        if (!$clientExists) {
            // Create new user and client
            $parts = explode(' ', $validated['client']['titulaire'], 2);
            $prenom = $parts[0] ?? '';
            $nom = $parts[1] ?? $prenom;

            $passwordPlain = Str::random(10);
            $activationCode = (string) random_int(100000, 999999);

            $user = User::create([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $validated['client']['email'],
                'telephone' => $validated['client']['telephone'],
                'password' => Hash::make($passwordPlain),
            ]);

            $client = Client::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'adresse' => $validated['client']['adresse'],
                'nci' => $validated['client']['nci'],
                'code_activation' => $activationCode,
                'is_active' => false,
            ]);

            $user->load('client');
        } else {
            // Use existing client
            $client = Client::find($validated['client']['id']);
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CLIENT_NOT_FOUND',
                        'message' => 'Client non trouvé',
                    ],
                ], 404);
            }
            $user = $client->user;
        }

        try {
            // Create account for the user
            $numero = Compte::generateNumero();
            $compteData = [
                'client_id' => $user->client->id,
                'numero_compte' => $numero,
                'user_id' => $user->id,
                'type_compte' => $validated['type'],
                'solde' => $validated['soldeInitial'],
                'devise' => $validated['devise'],
                'statut_compte' => 'actif',
                'date_creation' => now(),
            ];

            $compte = Compte::create($compteData);

            // Send notifications
            try {
                if (!$clientExists) {
                    // New client: send activation code
                    if (!empty($user->email)) {
                        Mail::raw("Bienvenue {$user->prenom}, votre mot de passe est : {$passwordPlain}. Votre code d'activation: {$activationCode}", function ($message) use ($user) {
                            $message->to($user->email)->subject('Création de votre compte');
                        });
                    }

                    if (!empty($user->telephone)) {
                        try {
                            $service = app(MessageServiceInterface::class);
                            $service->sendMessage($user->telephone, "Votre code d'activation est {$activationCode}");
                        } catch (\Throwable $e) {
                            Log::info("SMS fallback for {$user->telephone}: Votre code d'activation est {$activationCode}");
                        }
                    }
                } else {
                    // Existing client: send account creation confirmation
                    if (!empty($user->email)) {
                        Mail::to($user->email)->send(new CompteCreationConfirmation($compte));
                    }

                    if (!empty($user->telephone)) {
                        try {
                            $service = app(MessageServiceInterface::class);
                            $service->sendMessage($user->telephone, "Votre compte {$compte->numero_compte} a été créé avec succès. Solde initial: {$compte->solde} {$compte->devise}");
                        } catch (\Throwable $e) {
                            Log::info("SMS fallback for {$user->telephone}: Compte créé avec solde {$compte->solde} {$compte->devise}");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erreur lors de l\'envoi des notifications', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => [
                    'id' => (string) $compte->id,
                    'numeroCompte' => $compte->numero_compte,
                    'titulaire' => $validated['client']['titulaire'],
                    'type' => $compte->type_compte,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'dateCreation' => $compte->created_at->toISOString(),
                    'statut' => $compte->statut_compte,
                    'metadata' => [
                        'derniereModification' => $compte->updated_at->toISOString(),
                        'version' => $compte->version ?? 1,
                    ],
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Account creation failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => ['general' => $e->getMessage()],
                ],
            ], 400);
        }
    }
}
