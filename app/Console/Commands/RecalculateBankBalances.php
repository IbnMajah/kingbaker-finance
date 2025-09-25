<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use Illuminate\Console\Command;

class RecalculateBankBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:recalculate-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all bank account balances based on opening balance and transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating bank account balances...');

        $bankAccounts = BankAccount::all();
        $updated = 0;

        foreach ($bankAccounts as $account) {
            $oldBalance = $account->current_balance;
            $account->updateBalance();

            if ($oldBalance != $account->current_balance) {
                $this->line("Updated {$account->name}: {$oldBalance} → {$account->current_balance}");
                $updated++;
            }
        }

        $this->info("Recalculated balances for {$updated} bank accounts.");

        return Command::SUCCESS;
    }
}
