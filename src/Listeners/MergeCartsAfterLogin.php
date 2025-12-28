<?php

declare(strict_types=1);

namespace Carton\Carton\Listeners;

use Carton\Carton\Facades\Carton;
use Illuminate\Auth\Events\Authenticated;

final class MergeCartsAfterLogin
{
    /**
     * Handle the event.
     */
    public function handle(Authenticated $event): void
    {
        $user = $event->user;

        Carton::mergeUserCart($user);
    }
}
