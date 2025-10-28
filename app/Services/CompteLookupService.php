<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Support\Facades\Log;

class CompteLookupService
{
    /**
     * Recherche un compte localement, puis dans la source serverless si non trouvé.
     * Retourne une instance de Compte ou null.
     */
    public function findByNumero(string $numeroCompte): ?Compte
    {
        // Recherche locale
        try {
            $compte = Compte::where('numero_compte', $numeroCompte)->first();
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la recherche locale de compte: '.$e->getMessage());
            $compte = null;
        }

        if ($compte) {
            // Retour local prioritaire
            return $compte;
        }

        // Fallback: rechercher dans la base serverless distante
        return $this->findInServerless($numeroCompte);
    }

    /**
     * Requête vers la base serverless (API distante). Placeholder —
     * implémentation dépend de l'intégration réelle (HTTP client, key, etc.).
     */
    private function findInServerless(string $numeroCompte): ?Compte
    {
        // TODO: Adapter l'appel HTTP client (Guzzle, Http::) vers la base distante.
        // Exemple simplifié utilisant Http facade (non activé ici) :
        // $resp = Http::withToken(config('services.serverless.token'))->get(config('services.serverless.url')."/comptes/{$numeroCompte}");
        // if ($resp->ok()) { return new Compte($resp->json()); }

        Log::info("Recherche serverless non implémentée pour compte {$numeroCompte}");
        return null;
    }
}
