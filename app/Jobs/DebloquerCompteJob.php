<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DebloquerCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->startOfDay();

        $comptes = Compte::whereNotNull('date_fin_blocage')
            ->whereDate('date_fin_blocage', '<=', $today)
            ->where('statut_compte', 'bloqué')
            ->get();

        foreach ($comptes as $compte) {
            try {
                $compte->statut_compte = 'actif';
                $compte->save();

                $compte->transactions()->update(['archived' => false]);

                Log::info('Compte débloqué automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors du déblocage automatique du compte', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
