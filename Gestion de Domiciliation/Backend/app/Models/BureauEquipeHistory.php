<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\SoftDeleteTrait;

class BureauEquipeHistory extends Model
{
    use HasFactory;
    use SoftDeleteTrait;
    protected $table = 'eryx_bureaux_equipe_histories';

    protected $fillable = [
        'bureaux_equipe_id',
        'modified_by',
        'modification_date',
        'old_values',
        'new_values',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'modification_date' => 'datetime',
    ];

    // Relations
    public function bureauEquipe()
    {
        return $this->belongsTo(BureauEquipe::class, 'bureaux_equipe_id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
