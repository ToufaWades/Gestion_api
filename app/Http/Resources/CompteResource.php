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
        $data = [
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

        // Pour les comptes épargne, afficher les dates de blocage
        if (($this->type_compte ?? $this->type) === 'epargne') {
            $data['dateDebutBlocage'] = optional($this->date_debut_blocage)->toDateString();
            $data['dateFinBlocage'] = optional($this->date_fin_blocage)->toDateString();
        }

        return $data;
    }
}
