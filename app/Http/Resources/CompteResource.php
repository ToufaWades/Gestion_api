<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'numeroCompte' => $this->numero_compte,
            'titulaire' => isset($this->client) ? trim(($this->client->nom ?? '') . ' ' . ($this->client->prenom ?? '')) : ($this->titulaire_compte ?? null),
            'type' => $this->type_compte ?? $this->type,
            'solde' => $this->solde,
            'devise' => $this->devise ?? 'FCFA',
            'dateCreation' => optional($this->date_creation ?? $this->created_at)->toIso8601String(),
            'statut' => $this->statut_compte ?? $this->statut,
            'motifBlocage' => $this->motif_blocage ?? null,
            'metadata' => [
                'derniereModification' => optional($this->updated_at)->toIso8601String(),
                'version' => $this->version ?? 1,
            ],
        ];
    }
}
