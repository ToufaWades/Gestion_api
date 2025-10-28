<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->notNull();
            $table->timestamps();
            // foreign key added in a separate migration
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
