<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ArchiverComptesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->startOfDay();

        // Archiver les comptes épargne bloqués dont la date de début de blocage est échue
        $comptesToArchive = Compte::where('type_compte', 'epargne')
            ->where('statut_compte', 'bloqué')
            ->whereNotNull('date_debut_blocage')
            ->whereDate('date_debut_blocage', '<=', $today)
            ->where('archived', false)
            ->where('statut_compte', '!=', 'fermé')
            ->get();

        foreach ($comptesToArchive as $compte) {
            try {
                $compte->archived = true;
                $compte->save();

                Log::info('Compte archivé automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'archivage automatique du compte', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }

        // Désarchiver les comptes épargne bloqués dont la date de fin de blocage est échue
        $comptesToUnarchive = Compte::where('type_compte', 'epargne')
            ->where('statut_compte', 'bloqué')
            ->whereNotNull('date_fin_blocage')
            ->whereDate('date_fin_blocage', '<=', $today)
            ->where('archived', true)
            ->get();

        foreach ($comptesToUnarchive as $compte) {
            try {
                $compte->archived = false;
                $compte->save();

                Log::info('Compte désarchivé automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors du désarchivage automatique du compte', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }
    }
}