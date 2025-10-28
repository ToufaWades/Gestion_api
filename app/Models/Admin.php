<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Admin extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'user_id', 'fonction'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
