<?php

namespace App\Observers;

use App\Models\Strategy;

class StrategyObserver
{
    /**
     * Handle the Strategy "created" event.
     *
     * @param  \App\Models\Strategy  $strategy
     * @return void
     */
    public function creating(Strategy $strategy)
    {
        //
        $strategy->code = $strategy->generateUniqueCode();
    }

    /**
     * Handle the Strategy "updated" event.
     *
     * @param  \App\Models\Strategy  $strategy
     * @return void
     */
    public function updated(Strategy $strategy)
    {
        //
    }

    /**
     * Handle the Strategy "deleted" event.
     *
     * @param  \App\Models\Strategy  $strategy
     * @return void
     */
    public function deleted(Strategy $strategy)
    {
        //
    }

    /**
     * Handle the Strategy "restored" event.
     *
     * @param  \App\Models\Strategy  $strategy
     * @return void
     */
    public function restored(Strategy $strategy)
    {
        //
    }

    /**
     * Handle the Strategy "force deleted" event.
     *
     * @param  \App\Models\Strategy  $strategy
     * @return void
     */
    public function forceDeleted(Strategy $strategy)
    {
        //
    }
}
