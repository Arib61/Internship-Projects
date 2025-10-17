<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SoftDeleteTrait;
class Gerant extends Model
{
     use SoftDeleteTrait;
     protected $table = 'eryx_gerants'; 

    protected $fillable = [
        'nom',
        'cin',
        'address',
        'date_naissance',
        'telephone',
        'email',
        'photo_url',
    ];

    public function clients()
{
    return $this->hasMany(Client::class, 'gerant_id');
}

}

