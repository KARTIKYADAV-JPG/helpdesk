<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use App\Listeners\LogMailSendingListener;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Grant 'admin' ability to users whose role is 'admin'.
        // This Gate is used by the UserController and any @can('admin') checks.
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Register SMTP Email Sending Terminal Log Listeners
        Event::listen(MessageSending::class, [LogMailSendingListener::class, 'handleSending']);
        Event::listen(MessageSent::class, [LogMailSendingListener::class, 'handleSent']);
    }
}
