<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\ArchiveTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ArchiveService
{
    /**
     * Archive toutes les transactions de la semaine courante dans MongoDB
     * @return int nombre de transactions archivées
     */
    public static function archiveTransactionsOfWeek()
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();
        $transactions = Transaction::whereBetween('created_at', [$start, $end])->get();
        $collection = 'transactions_semaine_' . $start->format('Y_m_d');
        $archived = 0;
        foreach ($transactions as $trx) {
            (new ArchiveTransaction)->setCollection($collection)->create($trx->toArray());
            $archived++;
        }
        Log::info("Archivage: $archived transactions archivées dans $collection");
        return $archived;
    }
}
