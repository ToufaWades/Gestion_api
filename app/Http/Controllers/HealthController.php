<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $result = [
            'ok' => false,
            'app_env' => env('APP_ENV'),
            'checks' => [],
        ];

        // Add non-sensitive env sanity checks to help remote debugging
        $envChecks = [];
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbName = env('DB_DATABASE');
        $dbUrl = env('DATABASE_URL') ?? env('DB_URL');

        $envChecks['db_host_present'] = !empty($dbHost);
        $envChecks['db_port_numeric'] = is_numeric($dbPort);
        $envChecks['db_database_present'] = !empty($dbName);
        $envChecks['database_url_present'] = !empty($dbUrl);

        // detect obviously malformed values that have been seen in deployments
    $suspect = false;
    $suspectPatterns = ['client_encoding=', 'dbname=', '${DB_PORT}'];
        foreach ($suspectPatterns as $p) {
            if ($dbHost && str_contains($dbHost, $p)) {
                $suspect = true;
            }
            if ($dbPort && str_contains($dbPort, $p)) {
                $suspect = true;
            }
            if ($dbUrl && str_contains($dbUrl, $p)) {
                $suspect = true;
            }
        }
        $envChecks['env_values_suspect'] = $suspect;

        $result['checks']['env_sanity'] = $envChecks;

        // Also include non-sensitive view of the effective DB config (from config()),
        // which helps detect cached/malformed values created at build time.
        try {
            $dbConfig = config('database.connections.pgsql', []);
            $safeDbConfig = [
                'host' => $dbConfig['host'] ?? null,
                'port' => $dbConfig['port'] ?? null,
                'database' => $dbConfig['database'] ?? null,
                'username' => $dbConfig['username'] ?? null,
                'sslmode' => $dbConfig['sslmode'] ?? null,
            ];
            $result['checks']['config_database'] = $safeDbConfig;
        } catch (\Throwable $e) {
            $result['checks']['config_database'] = 'error';
            $result['checks']['config_database_error'] = $e->getMessage();
        }

        // DB connection
        try {
            DB::connection()->getPdo();
            $result['checks']['db_connection'] = true;
        } catch (\Throwable $e) {
            $result['checks']['db_connection'] = false;
            $result['checks']['db_error'] = $e->getMessage();
            Log::error('Health check DB connection failed: '.$e->getMessage());
        }

        // check important tables
        $tables = ['users', 'comptes', 'account_transactions'];
        foreach ($tables as $t) {
            try {
                $exists = Schema::hasTable($t);
                $result['checks']["table_{$t}"] = $exists;
                if ($exists) {
                    // try a small count to ensure permissions
                    try {
                        $count = DB::table($t)->limit(1)->count();
                        $result['checks']["table_{$t}_count_sample"] = $count;
                    } catch (\Throwable $e) {
                        $result['checks']["table_{$t}_count_error"] = $e->getMessage();
                        Log::error("Health check table {$t} count failed: {$e->getMessage()}");
                    }
                }
            } catch (\Throwable $e) {
                $result['checks']["table_{$t}"] = 'error';
                $result['checks']["table_{$t}_error"] = $e->getMessage();
                Log::error("Health check table {$t} error: {$e->getMessage()}");
            }
        }

        $result['ok'] = ($result['checks']['db_connection'] ?? false) === true;

        return response()->json($result, $result['ok'] ? 200 : 500);
    }
}
