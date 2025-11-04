<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArchiveService;

class ArchiveTransactionsWeek extends Command
{
    protected $signature = 'archive:week';
    protected $description = 'Archive les transactions de la semaine courante dans MongoDB';

    public function handle()
    {
        $count = ArchiveService::archiveTransactionsOfWeek();
        $this->info("$count transactions archivées dans MongoDB.");
    }
}
