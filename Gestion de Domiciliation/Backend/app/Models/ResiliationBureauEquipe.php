<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResiliationBureauEquipe extends Model
{
    use SoftDeletes;

    protected $table = 'eryx_resiliations_bureaux_equipe';

    protected $fillable = [
        'bureaux_equipe_id',
        'date_resiliation',
        'raison',
        'status',
        'created_by',
    ];

    public function bureauEquipe()
    {
        return $this->belongsTo(BureauEquipe::class, 'bureaux_equipe_id');
    }
}
