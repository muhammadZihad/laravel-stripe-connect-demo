<?php

namespace App\Console\Commands;

use App\Services\StripeService;
use Illuminate\Console\Command;

class ReconcilePendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:reconcile-pending 
                            {--minutes=10 : Only check transactions older than this many minutes}
                            {--dry-run : Show what would be reconciled without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile pending transactions with Stripe (handles abandoned 3DS flows)';

    /**
     * Execute the console command.
     */
    public function handle(StripeService $stripeService)
    {
        $minutes = $this->option('minutes');
        $dryRun = $this->option('dry-run');

        $this->info("Reconciling pending transactions older than {$minutes} minutes...");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $result = $stripeService->reconcilePendingTransactions($minutes);

        if ($result['success']) {
            $this->info("\nReconciliation Summary:");
            $this->info("- Transactions checked: {$result['checked']}");
            $this->info("- Successfully reconciled: " . count($result['reconciled']));
            $this->info("- Failed: " . count($result['failed']));

            if (count($result['reconciled']) > 0) {
                $this->info("\nReconciled Transactions:");
                foreach ($result['reconciled'] as $item) {
                    $this->line("  - Transaction #{$item['transaction_id']}: {$item['status']}");
                }
            }

            if (count($result['failed']) > 0) {
                $this->warn("\nFailed Reconciliations:");
                foreach ($result['failed'] as $item) {
                    $this->error("  - Transaction #{$item['transaction_id']}: {$item['error']}");
                }
            }

            if ($result['checked'] === 0) {
                $this->info('No pending transactions found to reconcile.');
            }

            return 0;
        }

        $this->error('Reconciliation failed');
        return 1;
    }
}

