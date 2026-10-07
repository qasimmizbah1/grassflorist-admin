<?php

namespace App\Console\Commands;

use App\Services\StoreImportService;
use Illuminate\Console\Command;

class StoreImportOrdersCommand extends Command
{
    protected $signature = 'store:import-orders {--per-page=50 : Number of orders per page} {--max-pages=0 : Maximum pages to import (0 for all)}';
    protected $description = 'Import orders, florist custom metadata, and order items from WooCommerce REST API';

    public function handle(StoreImportService $importService): int
    {
        $this->info('📦 Starting Orders & Florist Metadata Sync from WooCommerce...');
        $perPage = (int) ($this->option('per-page') ?: 50);
        $maxPages = (int) ($this->option('max-pages') ?: 0);
        $totalImported = 0;

        try {
            $initial = $importService->importOrdersChunk(1, $perPage);
            $totalPages = $initial['total_pages'];
            $totalRecords = $initial['total_records'];
            $totalImported += $initial['imported'];

            $pagesToProcess = ($maxPages > 0 && $maxPages < $totalPages) ? $maxPages : $totalPages;

            $this->info("Found {$totalRecords} total orders ({$pagesToProcess} page(s) to process).");
            $bar = $this->output->createProgressBar($pagesToProcess);
            $bar->start();
            $bar->advance();

            for ($page = 2; $page <= $pagesToProcess; $page++) {
                $res = $importService->importOrdersChunk($page, $perPage);
                $totalImported += $res['imported'];
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Successfully imported/updated {$totalImported} orders & order items!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Orders sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
