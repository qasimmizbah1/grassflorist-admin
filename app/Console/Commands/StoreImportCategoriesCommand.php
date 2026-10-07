<?php

namespace App\Console\Commands;

use App\Services\StoreImportService;
use Illuminate\Console\Command;

class StoreImportCategoriesCommand extends Command
{
    protected $signature = 'store:import-categories {--per-page=100 : Number of categories per page}';
    protected $description = 'Import categories and parent hierarchy from WooCommerce REST API';

    public function handle(StoreImportService $importService): int
    {
        $this->info('🌸 Starting Categories Sync from WooCommerce...');
        $perPage = (int) ($this->option('per-page') ?: 100);
        $page = 1;
        $totalImported = 0;

        try {
            $initial = $importService->importCategoriesChunk(1, $perPage);
            $totalPages = $initial['total_pages'];
            $totalRecords = $initial['total_records'];
            $totalImported += $initial['imported'];

            $this->info("Found {$totalRecords} total categories across {$totalPages} page(s).");
            $bar = $this->output->createProgressBar($totalPages);
            $bar->start();
            $bar->advance();

            for ($page = 2; $page <= $totalPages; $page++) {
                $res = $importService->importCategoriesChunk($page, $perPage);
                $totalImported += $res['imported'];
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Successfully imported/updated {$totalImported} categories!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Categories sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
