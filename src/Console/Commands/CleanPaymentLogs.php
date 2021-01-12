<?php

namespace Bjerke\Ecommerce\Console\Commands;

use Bjerke\Ecommerce\Models\PaymentLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanPaymentLogs extends Command
{
    protected $signature = 'ecommerce:clean-payment-logs {--older-than=}';

    protected $description = 'Deletes payment logs older than --older-than (number of days) option or ecommerce.payments.log_ttl from config';

    public function handle(): void
    {
        $ttlOption = $this->option('older-than') ?: config('ecommerce.orders.log_ttl');
        $ttl = Carbon::now()->subDays((int) $ttlOption);
        PaymentLog::where('created_at', '<', $ttl)->delete();
    }
}
