<?php

namespace App\Providers;

use App\Models\CustomTrade;
use App\Models\Strategy;
use App\Models\User;
use App\Observers\CustomTradeObserver;
use App\Observers\StrategyObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
        User::observe(UserObserver::class);
        CustomTrade::observe(CustomTradeObserver::class);
        Strategy::observe(StrategyObserver::class);
    }
}
