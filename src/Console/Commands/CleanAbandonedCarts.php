<?php

namespace Bjerke\Ecommerce\Console\Commands;

use Bjerke\Ecommerce\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CleanAbandonedCarts extends Command
{
    protected $signature = 'ecommerce:clean-abandoned-carts';

    protected $description = 'Deletes all expired carts';

    public function handle(): void
    {
        $ttl = Carbon::now()->subMinutes(config('ecommerce.cart.ttl'));
        Cart::where('updated_at', '<', $ttl)->chunk(100, function (Collection $collection) {
            $collection->each(fn(Cart $cart) => $cart->delete());
        });
    }
}
