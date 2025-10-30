<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeActivationToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'code_activation')) {
                $table->string('code_activation', 16)->nullable();
            }
            if (!Schema::hasColumn('clients', 'is_active')) {
                $table->boolean('is_active')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'code_activation')) {
                $table->dropColumn('code_activation');
            }
            if (Schema::hasColumn('clients', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}
