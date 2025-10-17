<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SoftDeleteTrait;
class Document extends Model
{
    use HasFactory;
use SoftDeleteTrait;
    protected $table = 'eryx_documents';

    protected $fillable = [
        'name',
        'path',
        'type',
        'size',
        'uploaded_by',
        'entity_type',
        'entity_id',
        'validity_start_date',
        'validity_end_date',
    ];

    protected $casts = [
        'validity_start_date' => 'date',
        'validity_end_date' => 'date',
    ];
public function uploader()
{
    return $this->belongsTo(User::class, 'uploader_id');
}
   
}
