<?php

namespace App\Models;

use App\Models\BureauEquipeHistory;
use App\Models\Client;
use App\Models\Gestionnaire;
use App\Models\User;
use App\Traits\SoftDeleteTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BureauEquipe extends Model
{
    use HasFactory;
    use SoftDeleteTrait;
    protected $table = 'eryx_bureaux_equipe';

    protected $fillable = [
        'client_id',
        'gestionnaire_id',
        'created_by',
        'date_debut',
        'date_fin',
        'renewal_count',
        'montant',
        'situation',
        'payement',
        'reference_numero',
        'notes',
        'is_deleted',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function gestionnaire()
    {
        return $this->belongsTo(Gestionnaire::class, 'gestionnaire_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(BureauEquipeHistory::class, 'bureaux_equipe_id');
    }
}
