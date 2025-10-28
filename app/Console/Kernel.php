<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageCompteJob;
use App\Jobs\DebloquerCompteJob;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Vérifier les blocages chaque jour
        $schedule->job(new VerifierBlocageCompteJob)->daily();
        $schedule->job(new DebloquerCompteJob)->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
