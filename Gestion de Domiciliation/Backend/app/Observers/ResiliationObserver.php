<?php

namespace App\Observers;

use App\Models\Resiliation;

class ResiliationObserver
{
    /**
     * Handle the Resiliation "created" event.
     */
    public function created(Resiliation $resiliation): void
    {
        //
    }

    /**
     * Handle the Resiliation "updated" event.
     */
    public function updated(Resiliation $resiliation): void
    {
        //
    }

    /**
     * Handle the Resiliation "deleted" event.
     */
    public function deleted(Resiliation $resiliation): void
    {
        //
    }

    /**
     * Handle the Resiliation "restored" event.
     */
    public function restored(Resiliation $resiliation): void
    {
        //
    }

    /**
     * Handle the Resiliation "force deleted" event.
     */
    public function forceDeleted(Resiliation $resiliation): void
    {
        //
    }

    public function creating(Resiliation $resiliation)
{
    $resiliation->date_fin = \Carbon\Carbon::parse($resiliation->date_resiliation)->addMonths(6);
}
}
