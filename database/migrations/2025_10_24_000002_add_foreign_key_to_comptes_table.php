<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // éviter que cette migration soit exécutée dans une transaction (Neon / PGDDL)
    public $withinTransaction = false;

    public function up(): void
    {
        // FK now created inline in create_comptes_table — ensure no leftover constraint before exiting
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS comptes DROP CONSTRAINT IF EXISTS comptes_user_id_foreign');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS comptes DROP CONSTRAINT IF EXISTS comptes_user_id_foreign');
    }
};
