<?php

namespace Bjerke\Ecommerce\Console\Commands;

use Bjerke\Ecommerce\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CleanAbandonedCarts extends Command
{
    protected $signature = 'ecommerce:clean-abandoned-carts {--older-than=}';

    protected $description = 'Deletes all expired carts older than --older-than (number of minutes) option or ecommerce.cart.ttl from config';

    public function handle(): void
    {
        $ttlOption = $this->option('older-than') ?: config('ecommerce.cart.ttl');
        $ttl = Carbon::now()->subMinutes((int) $ttlOption);
        Cart::where('updated_at', '<', $ttl)->chunk(100, function (Collection $collection) {
            $collection->each(fn(Cart $cart) => $cart->delete());
        });
    }
}
