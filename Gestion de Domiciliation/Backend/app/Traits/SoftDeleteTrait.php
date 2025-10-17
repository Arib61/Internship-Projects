<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait SoftDeleteTrait
{
    public static function bootSoftDeleteTrait()
    {
        if (static::class !== \App\Models\Payment::class) {
            static::addGlobalScope('softDeletes', function ($builder) {
                $builder->whereNull('deleted_at');
            });
        }
    }

    protected function initializeSoftDeleteTrait()
    {
        if (static::class !== \App\Models\Payment::class) {
            $this->casts['deleted_at'] = 'datetime';
        }
    }
}
