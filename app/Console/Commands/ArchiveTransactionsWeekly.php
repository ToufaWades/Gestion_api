<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ArchiveTransactionsWeekJob;
use Carbon\Carbon;

class ArchiveTransactionsWeekly extends Command
{
    protected $signature = 'transactions:archive-weekly';
    protected $description = 'Archive les transactions de la semaine dans MongoDB';

    public function handle()
    {
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        ArchiveTransactionsWeekJob::dispatch($weekStart, $weekEnd);
        $this->info('Archivage des transactions de la semaine lancé.');
    }
}
