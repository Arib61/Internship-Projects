<?php

namespace App\Models;

use App\Models\Domiciliation;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\SoftDeleteTrait;
class Resiliation extends Model
{
      use SoftDeleteTrait;
    protected $table = 'eryx_resiliations';

    protected $fillable = [
        'domiciliation_id',
        'date_resiliation',
        'raison',
        'created_by',
        'status',

     
    
    ];
public function domiciliation()
{
    return $this->belongsTo(Domiciliation::class, 'domiciliation_id');
}



public static function boot()
{
    parent::boot();

    static::creating(function ($resiliation) {
        if (empty($resiliation->date_fin) && !empty($resiliation->date_resiliation)) {
            $resiliation->date_fin = Carbon::parse($resiliation->date_resiliation)->addMonths(6);
        }
    });
}

}

