<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::table('migrations')->orderBy('id')->get();
foreach ($rows as $r) {
    echo "{$r->id} | {$r->migration} | batch={$r->batch}\n";
}
