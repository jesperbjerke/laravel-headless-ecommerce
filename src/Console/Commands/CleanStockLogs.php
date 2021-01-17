<?php

namespace Bjerke\Ecommerce\Console\Commands;

use Bjerke\Ecommerce\Models\OrderLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanStockLogs extends Command
{
    protected $signature = 'ecommerce:clean-stock-logs {--older-than=}';

    protected $description = 'Deletes stock logs older than --older-than (number of days) option or ecommerce.stock.log_ttl from config';

    public function handle(): void
    {
        $ttlOption = $this->option('older-than') ?: config('ecommerce.orders.log_ttl');
        $ttl = Carbon::now()->subDays((int) $ttlOption);
        OrderLog::where('created_at', '<', $ttl)->delete();
    }
}
