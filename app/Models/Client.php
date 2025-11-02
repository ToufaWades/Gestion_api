<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Compte;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'nom', 'prenom', 'email', 'telephone', 'user_id', 'adresse', 'nci',
        'code_activation', 'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comptes()
    {
        return $this->hasMany(Compte::class, 'client_id');
    }
}
