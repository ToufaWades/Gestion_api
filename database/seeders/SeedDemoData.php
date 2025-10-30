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
        // Create 5 users and clients
        $users = [];
        $clients = [];
        for ($i = 1; $i <= 5; $i++) {
            $email = "user{$i}@gmail.com";
            $users[] = [
                'nom' => 'User'.$i,
                'prenom' => 'Demo'.$i,
                'email' => $email,
                'telephone' => '+22177' . rand(1000000, 9999999),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('users')->upsert($users, ['email'], ['nom', 'prenom', 'telephone', 'password', 'updated_at']);

        $createdUsers = DB::table('users')->where('email', 'like', 'user%@gmail.com')->get();

        foreach ($createdUsers as $user) {
            $clients[] = [
                'user_id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'telephone' => $user->telephone,
                'adresse' => 'Adresse ' . $user->id,
                'nci' => '1' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                'code_activation' => '123456',
                'is_active' => 'true',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('clients')->upsert($clients, ['user_id'], ['nom','prenom','email','telephone','adresse','nci','code_activation','is_active','updated_at']);

        $createdClients = DB::table('clients')->pluck('id')->all();

        // For each client, create 1-3 comptes
        $comptes = [];
        foreach ($createdClients as $clientId) {
            $count = rand(1, 3);
            // Récupérer l'user_id associé au client
            $client = DB::table('clients')->where('id', $clientId)->first();
            $userId = $client ? $client->user_id : null;
            for ($j = 0; $j < $count; $j++) {
                $numero = 'C' . str_pad((string) rand(100000, 999999), 8, '0', STR_PAD_LEFT);
                $type = rand(0,1) ? 'epargne' : 'cheque';
                $comptes[] = [
                    'numero_compte' => $numero,
                    'titulaire_compte' => 'Titulaire '.$clientId,
                    'type_compte' => $type,
                    'devise' => 'CFA',
                    'date_creation' => now()->toDateString(),
                    'statut_compte' => 'actif',
                    'motif_blocage' => null,
                    'version' => 1,
                    'client_id' => $clientId,
                    'user_id' => $userId,
                    'solde' => rand(0, 2000000) / 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('comptes')->upsert(
            $comptes,
            ['numero_compte'],
            ['titulaire_compte','type_compte','devise','date_creation','statut_compte','motif_blocage','version','client_id','solde','updated_at']
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
