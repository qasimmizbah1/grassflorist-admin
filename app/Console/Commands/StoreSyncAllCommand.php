<?php

namespace App\Console\Commands;

use App\Services\StoreImportService;
use Illuminate\Console\Command;

class StoreSyncAllCommand extends Command
{
    protected $signature = 'store:sync-all {--clean : Wipe old dummy bookstore data before sync} {--fast : Import recent chunks first}';
    protected $description = 'Complete one-command sync for Categories, Products, Customers, and Orders from WooCommerce';

    public function handle(StoreImportService $importService): int
    {
        $this->info('===========================================================');
        $this->info('  GRASS FLORIST - WOOCOMMERCE COMPLETE SYNC ENGINE');
        $this->info('===========================================================');

        if ($this->option('clean')) {
            $this->warn('⚠️  Cleaning old dummy store data...');
            $summary = $importService->cleanOldData();
            foreach ($summary as $key => $count) {
                $this->line("  -> Cleaned: {$key} = {$count}");
            }
            $this->info('Old data cleaned successfully.');
            $this->newLine();
        }

        // 1. Categories
        $this->call('store:import-categories');
        $this->newLine();

        // 2. Products
        $this->call('store:import-products');
        $this->newLine();

        // 3. Customers
        $this->call('store:import-customers');
        $this->newLine();

        // 4. Orders
        $this->call('store:import-orders');
        $this->newLine();

        // 5. Download Images locally
        $this->call('store:download-images');
        $this->newLine();

        $this->info('===========================================================');
        $this->info('🎉 FULL STORE SYNC COMPLETED SUCCESSFULLY!');
        $this->info('===========================================================');

        return Command::SUCCESS;
    }
}
