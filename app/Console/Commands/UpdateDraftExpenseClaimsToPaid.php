<?php

namespace App\Console\Commands;

use App\Models\ExpenseClaim;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateDraftExpenseClaimsToPaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expense-claims:update-draft-to-paid
                            {--dry-run : Preview changes without actually updating}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all expense claims with status "draft" to "paid"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for draft expense claims...');

        // Count draft expense claims
        $draftCount = ExpenseClaim::where('status', 'draft')->count();

        if ($draftCount === 0) {
            $this->info('No expense claims with status "draft" found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$draftCount} expense claim(s) with status 'draft'.");

        // Show some examples if dry-run
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();

            $examples = ExpenseClaim::where('status', 'draft')
                ->with('user')
                ->limit(5)
                ->get(['id', 'reference_id', 'title', 'total', 'user_id', 'claim_date']);

            if ($examples->count() > 0) {
                $this->table(
                    ['ID', 'Reference', 'Title', 'Total', 'User', 'Claim Date'],
                    $examples->map(function ($claim) {
                        return [
                            $claim->id,
                            $claim->reference_id,
                            $claim->title,
                            number_format($claim->total, 2),
                            $claim->user ? $claim->user->name : 'N/A',
                            $claim->claim_date->format('Y-m-d'),
                        ];
                    })->toArray()
                );
            }

            if ($draftCount > 5) {
                $this->line("... and " . ($draftCount - 5) . " more expense claim(s)");
            }

            $this->newLine();
            $this->info("Would update {$draftCount} expense claim(s) from 'draft' to 'paid'.");
            return Command::SUCCESS;
        }

        // Confirm before proceeding
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to update {$draftCount} expense claim(s) from 'draft' to 'paid'?", true)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Updating expense claims...');

        // Use transaction for safety
        DB::beginTransaction();

        try {
            $updated = ExpenseClaim::where('status', 'draft')
                ->update(['status' => 'paid']);

            DB::commit();

            $this->newLine();
            $this->info("✓ Successfully updated {$updated} expense claim(s) from 'draft' to 'paid'.");

            // Verify the update
            $remainingDrafts = ExpenseClaim::where('status', 'draft')->count();
            if ($remainingDrafts > 0) {
                $this->warn("Note: {$remainingDrafts} expense claim(s) still have status 'draft'. This may be expected if new drafts were created during the update.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred while updating expense claims: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
