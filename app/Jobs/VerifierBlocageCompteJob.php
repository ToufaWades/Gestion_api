<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class VerifierBlocageCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->startOfDay();

        $comptes = Compte::whereNotNull('date_debut_blocage')
            ->whereDate('date_debut_blocage', '<=', $today)
            ->where(function ($q) {
                $q->whereNull('statut_compte')->orWhere('statut_compte', '!=', 'bloqué');
            })->get();

        foreach ($comptes as $compte) {
            try {
                $compte->statut_compte = 'bloqué';
                $compte->save();

                $compte->transactions()->update(['archived' => true]);

                Log::info('Compte bloqué automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors du blocage automatique du compte', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
