<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreDownloadImagesCommand extends Command
{
    protected $signature = 'store:download-images {--force : Re-download images even if file already exists locally}';
    protected $description = 'Download all product, gallery, and category images from live site to local public storage';

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $this->info('===========================================================');
        $this->info('  GRASS FLORIST - ASSET MEDIA DOWNLOADER');
        $this->info('===========================================================');

        $force = (bool) $this->option('force');
        $disk = Storage::disk('public');

        // Ensure directories exist
        $disk->makeDirectory('categories');
        $disk->makeDirectory('products');
        $disk->makeDirectory('products/gallery');

        // 1. Download Category Images
        $this->downloadCategoryImages($disk, $force);
        $this->newLine();

        // 2. Download Product Featured & Gallery Images
        $this->downloadProductImages($disk, $force);
        $this->newLine();

        $this->info('===========================================================');
        $this->info('🎉 ALL ASSETS DOWNLOADED & LOCAL PATHS UPDATED SUCCESSFULLY!');
        $this->info('===========================================================');

        return Command::SUCCESS;
    }

    protected function downloadCategoryImages($disk, bool $force): void
    {
        $categories = Category::whereNotNull('cat_image')
            ->where(function ($q) {
                $q->where('cat_image', 'LIKE', 'http://%')
                  ->orWhere('cat_image', 'LIKE', 'https://%');
            })->get();

        $this->info("📁 Found {$categories->count()} categories with external images.");
        if ($categories->isEmpty()) {
            return;
        }

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($categories as $cat) {
            $url = $cat->cat_image;
            $localPath = $this->downloadFile($url, 'categories', $disk, $force);

            if ($localPath) {
                $cat->cat_image = $localPath;
                $cat->save();
                $success++;
            } else {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  -> Categories: {$success} downloaded/updated, {$failed} failed.");
    }

    protected function downloadProductImages($disk, bool $force): void
    {
        $query = Product::where(function ($q) {
            $q->where('image', 'LIKE', 'http://%')
              ->orWhere('image', 'LIKE', 'https://%')
              ->orWhereNotNull('gallery');
        });

        $totalCount = $query->count();
        $this->info("📦 Found {$totalCount} products to process for images/gallery.");
        if ($totalCount === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $featuredDownloaded = 0;
        $galleryDownloaded = 0;

        $query->chunkById(25, function ($products) use ($disk, $force, $bar, &$featuredDownloaded, &$galleryDownloaded) {
            foreach ($products as $product) {
                $dirty = false;

                // 1. Featured Image
                if ($product->image && (str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://'))) {
                    $localFeatured = $this->downloadFile($product->image, 'products', $disk, $force);
                    if ($localFeatured) {
                        $product->image = $localFeatured;
                        $dirty = true;
                        $featuredDownloaded++;
                    }
                }

                // 2. Gallery Images
                $gallery = $product->gallery;
                if (is_array($gallery) && ! empty($gallery)) {
                    $newGallery = [];
                    $galleryChanged = false;

                    foreach ($gallery as $imgUrl) {
                        if (is_string($imgUrl) && (str_starts_with($imgUrl, 'http://') || str_starts_with($imgUrl, 'https://'))) {
                            $localGalleryPath = $this->downloadFile($imgUrl, 'products/gallery', $disk, $force);
                            if ($localGalleryPath) {
                                $newGallery[] = $localGalleryPath;
                                $galleryChanged = true;
                                $galleryDownloaded++;
                            } else {
                                $newGallery[] = $imgUrl;
                            }
                        } else {
                            $newGallery[] = $imgUrl;
                        }
                    }

                    if ($galleryChanged) {
                        $product->gallery = $newGallery;
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $product->save();
                }

                $bar->advance();
            }

            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();
        $this->info("  -> Products: {$featuredDownloaded} featured images, {$galleryDownloaded} gallery images processed.");
    }

    /**
     * Download a file from a URL and save to the given directory on disk.
     */
    protected function downloadFile(string $url, string $directory, $disk, bool $force = false): ?string
    {
        $url = trim($url);
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Extract filename from URL
        $parsedUrl = parse_url($url, PHP_URL_PATH);
        $originalFilename = basename($parsedUrl);
        $cleanFilename = urldecode($originalFilename);

        // Sanitize filename to avoid weird character issues
        $info = pathinfo($cleanFilename);
        $name = Str::slug($info['filename'] ?? 'file');
        $ext = strtolower($info['extension'] ?? 'jpg');
        if (empty($ext) || strlen($ext) > 5) {
            $ext = 'jpg';
        }

        $targetFilename = "{$name}.{$ext}";
        $relativePath = "{$directory}/{$targetFilename}";

        // If file already exists locally and force is false, just return relative path
        if (! $force && $disk->exists($relativePath)) {
            return $relativePath;
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->get($url);

            if ($response->successful()) {
                $disk->put($relativePath, $response->body());
                return $relativePath;
            }
        } catch (\Exception $e) {
            // Silently continue or log if needed
        }

        return null;
    }
}
