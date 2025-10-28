<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('account_transactions', 'archived')) {
                $table->boolean('archived')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('account_transactions', 'archived')) {
                $table->dropColumn('archived');
            }
        });
    }
};
