<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Disable wrapping this migration in a transaction. Postgres DDL and
     * certain session operations are safer when executed outside a
     * transaction for this specific change.
     */
    public $withinTransaction = false;
    /**
     * Run the migrations.
     * Add a nullable user_id column to the sessions table if missing.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sessions') && ! Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                // Use string to be compatible with UUID or numeric IDs
                $table->string('user_id')->nullable()->after('last_activity');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
