<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DesarchiverComptesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->startOfDay();

        // Désarchiver les comptes épargne bloqués dont la date de fin de blocage est échue
        $comptesToDesarchive = Compte::where('type_compte', 'epargne')
            ->where('statut_compte', 'bloqué')
            ->whereNotNull('date_fin_blocage')
            ->whereDate('date_fin_blocage', '<=', $today)
            ->where('archived', true)
            ->get();

        foreach ($comptesToDesarchive as $compte) {
            try {
                $compte->archived = false;
                $compte->save();
                Log::info('Compte désarchivé automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur désarchivage', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
