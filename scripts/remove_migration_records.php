<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$migs = [
    '0001_01_01_000000_create_users_table',
    '2025_10_23_104338_create_comptes_table',
    '2025_10_23_150001_create_admins_table',
    '2025_10_23_150002_create_clients_table',
];
foreach ($migs as $m) {
    $deleted = DB::table('migrations')->where('migration', $m)->delete();
    echo "deleted $deleted rows for $m\n";
}
