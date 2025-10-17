<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SoftDeleteTrait;

class Societe extends Model {

    use SoftDeleteTrait;
    protected $table = 'eryx_societe';

    protected $fillable = [
        'societe_nom',
        'president_date_naissance',
        'president_cin',
        'president_adresse',
        'nom_complet_francais',
        'nom_complet_arabe',
        'adresse_siege_social_francais',
        'adresse_siege_social_arabe',
        'ice',
        'rc',
        'identifiant_fiscal',
        'tp',
        'telephone',
        'email',
        'logo',
        'icon',
        'website',
        'capital_social',
        'type_entreprise',
        'form_juridique'
    ];
}