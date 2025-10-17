<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SoftDeleteTrait;
class Gestionnaire extends Model
{ 
     use SoftDeleteTrait;
    protected $table = 'eryx_gestionnaires';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'user_id',
    ];
       public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
