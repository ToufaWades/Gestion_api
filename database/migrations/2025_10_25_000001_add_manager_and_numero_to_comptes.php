<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            if (!Schema::hasColumn('comptes', 'numero_compte')) {
                $table->string('numero_compte')->unique()->nullable();
            }
            if (!Schema::hasColumn('comptes', 'is_admin_managed')) {
                $table->boolean('is_admin_managed')->default(false);
            }
            if (!Schema::hasColumn('comptes', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            if (Schema::hasColumn('comptes', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->dropColumn('manager_id');
            }
            if (Schema::hasColumn('comptes', 'is_admin_managed')) {
                $table->dropColumn('is_admin_managed');
            }
            if (Schema::hasColumn('comptes', 'numero_compte')) {
                //$table->dropUnique(['numero_compte']);
                $table->dropColumn('numero_compte');
            }
        });
    }
};
