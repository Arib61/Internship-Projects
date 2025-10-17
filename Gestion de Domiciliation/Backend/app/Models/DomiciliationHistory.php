<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Gestionnaire;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomiciliationHistory extends Model
{
    use HasFactory;

    protected $table = 'eryx_domiciliation_histories';

    protected $fillable = [
        'domiciliation_id',
        'modified_by',
        'modification_date',
        'old_values',
        'new_values',
        'notes'
    ];

    protected $casts = [
        'modification_date' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    /**
     * Relation avec la domiciliation
     */
    public function domiciliation()
    {
        return $this->belongsTo(Domiciliation::class, 'domiciliation_id');
    }

    /**
     * Relation avec l'utilisateur qui a modifié
     */
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
    
    public function client()
{
    return $this->belongsTo(Client::class);
}

 public function gestionnaire()
    {
        return $this->belongsTo(Gestionnaire::class);
    }

}