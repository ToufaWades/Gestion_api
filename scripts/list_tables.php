<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::select("select tablename from pg_catalog.pg_tables where schemaname = current_schema();");
foreach ($rows as $r) {
    echo $r->tablename . "\n";
}
