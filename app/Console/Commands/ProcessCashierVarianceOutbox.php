<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCashierVarianceOutbox as ProcessOutboxJob;
use App\Models\CashierVarianceOutbox;
use Illuminate\Console\Command;
use Throwable;

class ProcessCashierVarianceOutbox extends Command
{
    protected $signature = 'cashier-variances:process-outbox {--limit=100} {--retry-failed}';

    protected $description = 'Dispatch pending cashier variance integration events';

    public function handle(): int
    {
        $events = CashierVarianceOutbox::query()
            ->whereIn('status', $this->option('retry-failed') ? ['pending', 'failed'] : ['pending'])
            ->where(fn ($query) => $query->whereNull('available_at')->orWhere('available_at', '<=', now()))
            ->orderBy('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        foreach ($events as $event) {
            try {
                ProcessOutboxJob::dispatch($event->id);
            } catch (Throwable $exception) {
                $this->warn("Event {$event->id} remains pending: {$exception->getMessage()}");
            }
        }

        $this->info("Dispatched {$events->count()} event(s).");

        return self::SUCCESS;
    }
}
