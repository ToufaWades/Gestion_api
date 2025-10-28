<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // necessary for Neon / PG: don't run inside a transaction
    public $withinTransaction = false;

    public function up(): void
    {
        // FK now created inline in create_transactions_table — ensure no leftover constraint before exiting
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS account_transactions DROP CONSTRAINT IF EXISTS account_transactions_compte_id_foreign');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS account_transactions DROP CONSTRAINT IF EXISTS account_transactions_compte_id_foreign');
    }
};
