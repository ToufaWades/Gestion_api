<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Compte;

class Transaction extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'transactions';
    // use integer autoincrement id to match the migration
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'montant', 'type', 'compte_id', 'archived'
    ];

    protected $casts = [
        'archived' => 'boolean',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }
}
