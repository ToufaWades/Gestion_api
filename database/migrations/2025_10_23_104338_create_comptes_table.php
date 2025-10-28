<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Avoid running DDL inside a transaction on managed Postgres (Neon)
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            // créer la FK inline pour éviter des migrations séparées sur PG managé
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->decimal('solde', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
