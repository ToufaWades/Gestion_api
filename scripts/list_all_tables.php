<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::select("select schemaname, tablename from pg_catalog.pg_tables where schemaname not in ('pg_catalog', 'information_schema') order by schemaname, tablename");
foreach ($rows as $r) {
    echo $r->schemaname . '.' . $r->tablename . "\n";
}
