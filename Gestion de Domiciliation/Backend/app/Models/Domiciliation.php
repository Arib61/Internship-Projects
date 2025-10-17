<?php

namespace App\Models;

use App\Models\Client;
use App\Models\DomiciliationHistory;
use App\Models\Resiliation;
use App\Models\User;
use App\Traits\SoftDeleteTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domiciliation extends Model
{
    use HasFactory;
    use SoftDeleteTrait;
    protected $table = 'eryx_domiciliations';

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
    ];

    protected $casts = [
        'payement' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];
    public function histories()
    {
        return $this->hasMany(DomiciliationHistory::class, 'domiciliation_id');
    }

    public function resiliations()
    {
        return $this->hasMany(Resiliation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function gestionnaire()
    {
        return $this->belongsTo(Gestionnaire::class, 'gestionnaire_id');
    }
        public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
