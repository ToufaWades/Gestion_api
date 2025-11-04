<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageCompteJob;
use App\Jobs\DebloquerCompteJob;
use App\Jobs\ArchiverComptesJob;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Archiver les comptes épargne bloqués dont la date de début de blocage est échue
        $schedule->job(new ArchiverComptesJob)->daily();

        // Désarchiver les comptes épargne bloqués dont la date de fin de blocage est échue
        $schedule->job(new \App\Jobs\DesarchiverComptesJob)->daily();

        // Archiver les transactions chaque semaine
        $this->commands([
            \App\Console\Commands\ArchiveTransactionsWeek::class,
            \App\Console\Commands\ArchiveTransactionsWeekly::class,
            \App\Console\Commands\CheckMongoTransactions::class,
        ]);
        $schedule->command('archive:week')->weeklyOn(7, '23:55');

        // Planification automatique de l'archivage des transactions chaque semaine
        $schedule->command('transactions:archive-weekly')->weeklyOn(1, '01:00'); // chaque lundi à 01h00
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\ArchiveTransactionsWeekly::class,
    ];
}
