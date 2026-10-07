<?php

namespace App\Console\Commands;

use App\Services\StoreImportService;
use Illuminate\Console\Command;

class StoreImportProductsCommand extends Command
{
    protected $signature = 'store:import-products {--per-page=50 : Number of products per page} {--max-pages=0 : Maximum pages to import (0 for all)}';
    protected $description = 'Import products, images, and category associations from WooCommerce REST API';

    public function handle(StoreImportService $importService): int
    {
        $this->info('💐 Starting Products Sync from WooCommerce...');
        $perPage = (int) ($this->option('per-page') ?: 50);
        $maxPages = (int) ($this->option('max-pages') ?: 0);
        $totalImported = 0;

        try {
            $initial = $importService->importProductsChunk(1, $perPage);
            $totalPages = $initial['total_pages'];
            $totalRecords = $initial['total_records'];
            $totalImported += $initial['imported'];

            $pagesToProcess = ($maxPages > 0 && $maxPages < $totalPages) ? $maxPages : $totalPages;

            $this->info("Found {$totalRecords} total products ({$pagesToProcess} page(s) to process).");
            $bar = $this->output->createProgressBar($pagesToProcess);
            $bar->start();
            $bar->advance();

            for ($page = 2; $page <= $pagesToProcess; $page++) {
                $res = $importService->importProductsChunk($page, $perPage);
                $totalImported += $res['imported'];
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Successfully imported/updated {$totalImported} products!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Products sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
