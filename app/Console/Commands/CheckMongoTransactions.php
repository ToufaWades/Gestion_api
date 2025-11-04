<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jenssegers\Mongodb\Connection;
use Illuminate\Support\Facades\DB;

class CheckMongoTransactions extends Command
{
    protected $signature = 'mongodb:check-transactions {collection}';
    protected $description = 'Vérifie la présence des transactions dans une collection MongoDB';

    public function handle()
    {
    $collection = $this->argument('collection');
    $conn = DB::connection('mongodb');
    $count = $conn->table($collection)->count();
    $this->info("La collection '$collection' contient $count transaction(s).");
    $sample = $conn->table($collection)->limit(3)->get();
    $this->line('Exemple de transactions :');
    $this->line(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
