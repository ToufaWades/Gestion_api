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
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
