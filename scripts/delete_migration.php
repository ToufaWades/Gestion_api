<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$mig = $argv[1] ?? null;
if (!$mig) {
    echo "Usage: php delete_migration.php <migration_name>\n";
    exit(1);
}
$deleted = DB::table('migrations')->where('migration', $mig)->delete();
echo "deleted $deleted rows for $mig\n";
