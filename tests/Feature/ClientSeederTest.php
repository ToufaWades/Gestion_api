<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Client;

class ClientSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_seeder_creates_clients_and_comptes()
    {
        $this->seed(\Database\Seeders\ClientSeeder::class);
        $this->assertDatabaseCount('clients', 10);
        $this->assertDatabaseHas('comptes', [
            // On vérifie qu'au moins un compte est lié à un client
            'client_id' => Client::first()->id
        ]);
    }
}
