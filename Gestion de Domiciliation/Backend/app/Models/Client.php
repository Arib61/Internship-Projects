<?php

namespace App\Models;

use App\Models\Gerant;
use App\Models\Gestionnaire;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SoftDeleteTrait;

class Client extends Model
{

    use SoftDeleteTrait;
    protected $table = 'eryx_clients';



    protected $fillable = [
        'nom_francais',
        'nom_arabe',
        'adresse_siege_social_francais',
        'adresse_siege_social_arabe',
        'ice',
        'cin',
        'certificat_negative',
        'rc',
        'identifiant_fiscal',
        'tp',
        'tribunal',
        'type_tribunal',
        'telephone',
        'email',
        'pays',
        'ville',
        'website',
        'capital_social',
        'type_entreprise',
        'type_client',
        'status',
        'gerant_id',
        'gestionnaire_id',
    ];

    public function gestionnaire()
    {
        return $this->belongsTo(Gestionnaire::class);
    }

    public function gerant()
    {
        return $this->belongsTo(Gerant::class, 'gerant_id');
    }
}
