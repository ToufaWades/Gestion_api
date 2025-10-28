<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedDemoData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'nom' => 'User'.$i,
                'prenom' => 'Demo'.$i,
                'email' => "user{$i}@example.com",
                'telephone' => '221' . rand(700000000, 799999999),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    // insert users idempotently (upsert on email)
    DB::table('users')->upsert($users, ['email'], ['nom', 'prenom', 'telephone', 'password', 'updated_at']);

    // récupérer les ids des users créés/présents (ceux avec email user{n}@example.com)
    $createdUsers = DB::table('users')->where('email', 'like', 'user%@example.com')->pluck('id')->all();

        // For each user, create 1-3 comptes (use column names expected by the app)
        $comptes = [];
        foreach ($createdUsers as $userId) {
            $count = rand(1, 3);
            for ($j = 0; $j < $count; $j++) {
                $numero = 'C' . str_pad((string) rand(100000, 999999), 8, '0', STR_PAD_LEFT);
                $type = rand(0,1) ? 'epargne' : 'cheque';
                $comptes[] = [
                    'numero_compte' => $numero,
                    'titulaire_compte' => 'Titulaire '.$userId,
                    'type_compte' => $type,
                    'devise' => 'CFA',
                    'date_creation' => now()->toDateString(),
                    'statut_compte' => 'actif',
                    'motif_blocage' => null,
                    'version' => 1,
                    'user_id' => $userId,
                    'solde' => rand(0, 2000000) / 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        // use upsert for idempotency: match on numero_compte
        DB::table('comptes')->upsert(
            $comptes,
            ['numero_compte'],
            ['titulaire_compte','type_compte','devise','date_creation','statut_compte','motif_blocage','version','user_id','solde','updated_at']
        );

        $createdComptes = DB::table('comptes')->pluck('id')->all();

        // Create some transactions for comptes
        $transactions = [];
        foreach ($createdComptes as $compteId) {
            $ops = rand(2, 6);
            for ($k = 0; $k < $ops; $k++) {
                $type = rand(0,1) ? 'depot' : 'retrait';
                $amount = rand(1000, 500000) / 100; // decimals
                $transactions[] = [
                    'montant' => $amount,
                    'type' => $type,
                    'compte_id' => $compteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('account_transactions')->insert($transactions);
    }
}
