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
            if (!Schema::hasColumn('comptes', 'statut_compte')) {
                $table->string('statut_compte')->default('actif')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'date_debut_blocage')) {
                $table->date('date_debut_blocage')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'date_fin_blocage')) {
                $table->date('date_fin_blocage')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'motif_blocage')) {
                $table->string('motif_blocage')->nullable();
            }
            if (!Schema::hasColumn('comptes', 'archived')) {
                $table->boolean('archived')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            if (Schema::hasColumn('comptes', 'statut_compte')) {
                $table->dropColumn('statut_compte');
            }
            if (Schema::hasColumn('comptes', 'date_debut_blocage')) {
                $table->dropColumn('date_debut_blocage');
            }
            if (Schema::hasColumn('comptes', 'date_fin_blocage')) {
                $table->dropColumn('date_fin_blocage');
            }
            if (Schema::hasColumn('comptes', 'motif_blocage')) {
                $table->dropColumn('motif_blocage');
            }
            if (Schema::hasColumn('comptes', 'archived')) {
                $table->dropColumn('archived');
            }
        });
    }
};
