<?php

namespace App\Console\Commands;

use App\Services\HrCashierSyncService;
use Illuminate\Console\Command;
use RuntimeException;

class SyncHrCashiers extends Command
{
    protected $signature = 'hr:sync-cashiers';

    protected $description = 'Refresh the local cashier cache from HR';

    public function handle(HrCashierSyncService $service): int
    {
        try {
            $count = $service->sync();
            $this->info("Refreshed {$count} active cashier records.");

            return self::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
