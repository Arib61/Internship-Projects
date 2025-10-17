<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
     protected $table = 'eryx_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'read',
        'data',
    ];

    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
    ];
}
