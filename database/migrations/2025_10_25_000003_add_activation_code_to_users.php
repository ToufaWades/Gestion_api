<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivationCodeToUsers extends Migration
{
    // Some Postgres hosts (Neon) require migrations that alter existing tables to be non-transactional
    public $withinTransaction = false;

    public function up()
    {
        if (! Schema::hasColumn('users', 'activation_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('activation_code')->nullable()->after('remember_token');
                $table->timestamp('activation_expires_at')->nullable()->after('activation_code');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'activation_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['activation_code', 'activation_expires_at']);
            });
        }
    }
}
