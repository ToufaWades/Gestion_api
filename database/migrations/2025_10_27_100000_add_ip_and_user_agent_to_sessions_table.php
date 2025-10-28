<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Run outside transaction for Postgres compatibility
    public $withinTransaction = false;

    public function up()
    {
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (! Schema::hasColumn('sessions', 'ip_address')) {
                    // 45 chars is enough for IPv6 textual representation
                    $table->string('ip_address', 45)->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('sessions', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (Schema::hasColumn('sessions', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
                if (Schema::hasColumn('sessions', 'ip_address')) {
                    $table->dropColumn('ip_address');
                }
            });
        }
    }
};
