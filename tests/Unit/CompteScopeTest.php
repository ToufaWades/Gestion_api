<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Compte;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompteScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function il_ne_retourne_que_les_comptes_non_archives()
    {
        $user = \App\Models\User::factory()->create();
        $client = \App\Models\Client::factory()->for($user)->create();
        $compteActif = Compte::factory()->create(['archived' => false, 'client_id' => $client->id]);
        $compteArchive = Compte::factory()->create(['archived' => true, 'client_id' => $client->id]);

        $comptes = Compte::all();
        $this->assertTrue($comptes->contains($compteActif));
        $this->assertFalse($comptes->contains($compteArchive));
    }

    /** @test */
    public function scope_numero_retourne_le_bon_compte()
    {
        $user = \App\Models\User::factory()->create();
        $client = \App\Models\Client::factory()->for($user)->create();
        $compte = Compte::factory()->create(['numero_compte' => 'ABC123', 'client_id' => $client->id]);
        $result = Compte::numero('ABC123')->first();
        $this->assertEquals($compte->id, $result->id);
    }

    /** @test */
    public function scope_client_retourne_les_comptes_du_client()
    {
        $user = \App\Models\User::factory()->create();
        $client = \App\Models\Client::factory()->for($user)->create(['telephone' => '771234567']);
        $compte = Compte::factory()->create(['client_id' => $client->id]);
        $result = Compte::client('771234567')->first();
        $this->assertEquals($compte->id, $result->id);
    }

    /** @test */
    public function scope_etat_filtre_par_statut()
    {
        $user = \App\Models\User::factory()->create();
        $client = \App\Models\Client::factory()->for($user)->create();
        $compte = Compte::factory()->create(['statut_compte' => 'actif', 'client_id' => $client->id]);
        $result = Compte::etat('actif')->first();
        $this->assertEquals($compte->id, $result->id);
    }

    /** @test */
    public function scope_type_filtre_par_type()
    {
        $user = \App\Models\User::factory()->create();
        $client = \App\Models\Client::factory()->for($user)->create();
        $compte = Compte::factory()->create(['type_compte' => 'épargne', 'client_id' => $client->id]);
        $result = Compte::type('épargne')->first();
        $this->assertEquals($compte->id, $result->id);
    }
}
