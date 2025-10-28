<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{

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
            'client.email' => 'required|email|unique:users,email',
            'client.telephone' => [
                'required',
                'string',
                'unique:users,telephone',
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

        try {
            // Extract client data
            $clientData = $validated['client'];
            // Split titulaire into nom prenom, assume "Prenom Nom"
            $parts = explode(' ', $clientData['titulaire'], 2);
            $prenom = $parts[0] ?? '';
            $nom = $parts[1] ?? $prenom;

            $userData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $clientData['email'],
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
                'date_naissance' => null, // not provided
                'nci' => $clientData['nci'],
            ];

            $compteData = [
                'type_compte' => $validated['type'],
                'solde' => $validated['soldeInitial'], // use soldeInitial as initial solde
                'devise' => $validated['devise'],
                'statut_compte' => 'actif',
                'date_creation' => now(),
            ];

            $user = User::createAccount($userData, $compteData);

            $compte = $user->client->comptes()->first(); // assume one compte

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => [
                    'id' => (string) $compte->id,
                    'numeroCompte' => $compte->numero_compte,
                    'titulaire' => $clientData['titulaire'],
                    'type' => $compte->type_compte,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'dateCreation' => $compte->created_at->toISOString(),
                    'statut' => $compte->statut_compte,
                    'metadata' => [
                        'derniereModification' => $compte->updated_at->toISOString(),
                        'version' => $compte->version ?? 1,
                    ],
                ],l
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
