<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $rows = DB::table('comptes')->whereNull('numero_compte')->get();

        foreach ($rows as $row) {
            do {
                // Generate a C-prefixed 8-digit number
                $numero = 'C'.sprintf('%08d', random_int(0, 99999999));
            } while (DB::table('comptes')->where('numero_compte', $numero)->exists());

            DB::table('comptes')->where('id', $row->id)->update(['numero_compte' => $numero]);
        }
    }

    public function down(): void
    {
        // Can't reliably revert generated numbers; leave as-is.
    }
};
