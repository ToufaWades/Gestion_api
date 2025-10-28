<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$tables = ['users','comptes','account_transactions'];
foreach ($tables as $t) {
    try {
        $c = DB::table($t)->count();
        echo "$t: $c\n";
    } catch (Exception $e) {
        echo "$t: error ({$e->getMessage()})\n";
    }
}
