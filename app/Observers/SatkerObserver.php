<?php

namespace App\Observers;

use App\Models\Satker;

class SatkerObserver
{
    // public function creating(Satker $satker): void
    // {
    //     $satker->user_email = auth()->user()->email;
    // }
    /**
     * Handle the Satker "created" event.
     */
    public function created(Satker $satker): void
    {
        //
    }

    /**
     * Handle the Satker "updated" event.
     */
    public function updated(Satker $satker): void
    {
        //
    }

    /**
     * Handle the Satker "deleted" event.
     */
    public function deleted(Satker $satker): void
    {
        //
    }

    /**
     * Handle the Satker "restored" event.
     */
    public function restored(Satker $satker): void
    {
        //
    }

    /**
     * Handle the Satker "force deleted" event.
     */
    public function forceDeleted(Satker $satker): void
    {
        //
    }
}
