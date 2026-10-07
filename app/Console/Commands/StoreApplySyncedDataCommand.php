<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class StoreApplySyncedDataCommand extends Command
{
    protected $signature = 'store:apply-synced-data';
    protected $description = 'Import synchronized bilingual categories/products database dump and unpack media assets';

    public function handle(): int
    {
        $this->info('===========================================================');
        $this->info('  GRASS FLORIST - APPLY SYNCHRONIZED STORE DATA & MEDIA');
        $this->info('===========================================================');

        // 1. Unpack Media Assets
        $zipPath = public_path('media_assets.zip');
        if (file_exists($zipPath)) {
            $this->info('📦 Unpacking media images into storage/app/public/...');
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $storagePublic = storage_path('app/public');
                if (! file_exists($storagePublic)) {
                    @mkdir($storagePublic, 0775, true);
                }
                $zip->extractTo($storagePublic);
                $zip->close();
                $this->info('✅ Media assets unpacked successfully.');
            } else {
                $this->warn('⚠️ Could not open media_assets.zip');
            }
        } else {
            $this->line('ℹ️ No media_assets.zip found, skipping unpack.');
        }

        // 2. Import SQL Dump
        $sqlPath = database_path('synced_categories_and_products.sql');
        if (file_exists($sqlPath)) {
            $this->info('🗄️ Importing synced database records (Categories & Products)...');
            $sql = file_get_contents($sqlPath);
            DB::unprepared($sql);
            $this->info('✅ Database updated with local image paths and bilingual content!');
        } else {
            $this->error('❌ SQL dump file not found at: ' . $sqlPath);
            return Command::FAILURE;
        }

        // 3. Storage Symlink
        $this->call('storage:link');

        $this->info('===========================================================');
        $this->info('🎉 COMPLETE STORE DATA & LOCAL IMAGES APPLIED SUCCESSFULLY!');
        $this->info('===========================================================');

        return Command::SUCCESS;
    }
}
