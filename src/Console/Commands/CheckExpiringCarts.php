<?php

namespace Bjerke\Ecommerce\Console\Commands;

use Bjerke\Ecommerce\Events\CartExpiring;
use Bjerke\Ecommerce\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CheckExpiringCarts extends Command
{
    protected $signature = 'ecommerce:check-expiring-carts';

    protected $description = 'Check carts that are about to expire and triggers event';

    public function handle(): void
    {
        $ttl = Carbon::now()->subMinutes(config('ecommerce.cart.trigger_expiring_event_after'));
        Cart::where('updated_at', '<', $ttl)->chunk(100, function (Collection $collection) {
            $collection->each(fn (Cart $cart) => CartExpiring::dispatch($cart));
        });
    }
}
