<?php
namespace App\Jobs;

use App\Models\Transaction;
use App\Models\TransactionMongo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

class ArchiveTransactionsWeekJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $weekStart;
    public $weekEnd;

    public function __construct($weekStart, $weekEnd)
    {
        $this->weekStart = $weekStart;
        $this->weekEnd = $weekEnd;
    }

    public function handle()
    {
        // Récupérer toutes les transactions de la semaine depuis PostgreSQL
        $transactions = Transaction::whereBetween('created_at', [$this->weekStart, $this->weekEnd])->get();
        $collectionName = 'transactions_semaine_' . Carbon::parse($this->weekStart)->format('Y_m_d');
        foreach ($transactions as $trx) {
            $mongo = new TransactionMongo($trx->toArray());
            $mongo->setConnection('mongodb');
            $mongo->setCollection($collectionName);
            $mongo->save();
        }
    }
}
