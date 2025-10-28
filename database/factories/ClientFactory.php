<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;


    public function definition()
    {
        return [
            'id' => (string) Str::uuid(),
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'email' => $this->faker->unique()->safeEmail,
            'telephone' => $this->faker->unique()->phoneNumber,
            'date_naissance' => $this->faker->date('Y-m-d'),
        ];
    }


    public function withUser()
    {
        return $this->for(\App\Models\User::factory());
    }
}
