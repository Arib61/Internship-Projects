<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Abonnement extends Model
{
    protected $fillable = ['date_debut', 'date_fin', 'jours_restants', 'statut'];

    public function isExpired()
    {
        return now()->greaterThan(Carbon::parse($this->date_fin));
    }
}
