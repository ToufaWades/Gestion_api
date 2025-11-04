<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as Eloquent;

class ArchiveTransaction extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = null; // sera défini dynamiquement pour chaque semaine

    protected $fillable = [
        'transaction_id',
        'compte_id',
        'client_id',
        'type', // depot ou retrait
        'montant',
        'solde_avant',
        'solde_apres',
        'description',
        'date',
        'agent',
        'created_at',
        'updated_at',
    ];

    // Permet de définir dynamiquement la collection MongoDB (ex: transactions_semaine_2025_11_02)
    public function setWeeklyCollection($date)
    {
        $semaine = date('Y_m_d', strtotime($date));
        $this->setCollection('transactions_semaine_' . $semaine);
    }
}
