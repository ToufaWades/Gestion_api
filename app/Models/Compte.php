<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Compte extends Model
{
    use HasFactory;

    protected $table = 'comptes';
    // use integer autoincrement id to match the database migration
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'numero_compte',
        'titulaire_compte',
        'type_compte',
        'devise',
        'date_creation',
        'statut_compte',
        'date_debut_blocage',
        'date_fin_blocage',
        'motif_blocage',
        'version',
        'user_id',
        'client_id',
        'manager_id',
        'is_admin_managed',
        'solde',
        'archived',
    ];

    protected $casts = [
        'date_creation' => 'date',
        'date_debut_blocage' => 'date',
        'date_fin_blocage' => 'date',
        'archived' => 'boolean',
        'solde' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('non_archived', function ($query) {
            // Use explicit SQL boolean literal to avoid PDO sending 0/1 which
            // PostgreSQL rejects for boolean comparison (boolean = integer).
            $query->whereRaw('archived = false');
        });

        // Generate a unique numero_compte when creating
        static::creating(function ($compte) {
            if (empty($compte->numero_compte)) {
                $compte->numero_compte = static::generateNumero();
            }
        });
    }

    // Scope pour rechercher par numéro de compte
    public function scopeNumero($query, $numero)
    {
        return $query->where('numero_compte', $numero);
    }

    // Scope pour rechercher les comptes liés à un client (par téléphone)
    public function scopeClient($query, $telephone)
    {
        return $query->whereHas('client', function ($q) use ($telephone) {
            $q->where('telephone', $telephone);
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'compte_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function getSoldeAttribute()
    {
        // If there are transactions, calculate from them
        if ($this->transactions()->exists()) {
            $depot = $this->transactions()->where('type', 'depot')->sum('montant');
            $retrait = $this->transactions()->where('type', 'retrait')->sum('montant');
            return $depot - $retrait;
        }

        // Otherwise return the stored solde value
        return $this->attributes['solde'] ?? 0;
    }

    public function scopeEtat($query, $statut)
    {
        return $query->where('statut_compte', $statut);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type_compte', $type);
    }

    /**
     * Generate a reasonably unique account number.
     * Format: ACC-YYYYMMDD-XXXX where XXXX is a random 4-digit number.
     */
    public static function generateNumero(): string
    {
        do {
            $numero = 'ACC-'.now()->format('Ymd').'-'.mt_rand(1000, 9999);
        } while (static::where('numero_compte', $numero)->exists());

        return $numero;
    }
}
