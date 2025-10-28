<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    // Not transactional for compatibility with some Postgres hosts
    public $withinTransaction = false;

    public function up()
    {
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->text('payload');
                $table->integer('last_activity');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
