<?php

namespace App\Observers;

use App\Models\CustomTrade;

class CustomTradeObserver
{
    /**
     * Handle the CustomTrade "created" event.
     *
     * @param  \App\Models\CustomTrade  $customTrade
     * @return void
     */
    public function created(CustomTrade $customTrade)
    {
        //
    }

    /**
     * Handle the CustomTrade "updated" event.
     *
     * @param  \App\Models\CustomTrade  $customTrade
     * @return void
     */
    public function updated(CustomTrade $customTrade)
    {
        //
    }

    /**
     * Handle the CustomTrade "deleted" event.
     *
     * @param  \App\Models\CustomTrade  $customTrade
     * @return void
     */
    public function deleted(CustomTrade $customTrade)
    {
        //
    }

    /**
     * Handle the CustomTrade "restored" event.
     *
     * @param  \App\Models\CustomTrade  $customTrade
     * @return void
     */
    public function restored(CustomTrade $customTrade)
    {
        //
    }

    /**
     * Handle the CustomTrade "force deleted" event.
     *
     * @param  \App\Models\CustomTrade  $customTrade
     * @return void
     */
    public function forceDeleted(CustomTrade $customTrade)
    {
        //
    }
}
