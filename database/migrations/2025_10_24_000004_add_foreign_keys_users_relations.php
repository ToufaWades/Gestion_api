<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // avoid transactional execution on PG managed services
    public $withinTransaction = false;

    public function up(): void
    {
        // All FKs are created inline in their create_* migrations. Ensure no leftover constraints remain.
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS admins DROP CONSTRAINT IF EXISTS admins_user_id_foreign');
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS clients DROP CONSTRAINT IF EXISTS clients_user_id_foreign');
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS comptes DROP CONSTRAINT IF EXISTS comptes_user_id_foreign');
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS account_transactions DROP CONSTRAINT IF EXISTS account_transactions_compte_id_foreign');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS admins DROP CONSTRAINT IF EXISTS admins_user_id_foreign');
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS clients DROP CONSTRAINT IF EXISTS clients_user_id_foreign');
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS comptes DROP CONSTRAINT IF EXISTS comptes_user_id_foreign');
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ 'ALTER TABLE IF EXISTS account_transactions DROP CONSTRAINT IF EXISTS account_transactions_compte_id_foreign');
    }
};
