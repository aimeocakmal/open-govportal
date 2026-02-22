<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizationService
{
    /** @var list<string> */
    private const OPTIMIZABLE_EXTENSIONS = [
        'jpeg', 'jpg', 'png', 'gif', 'bmp', 'tiff', 'tif',
    ];

    private const MAX_WIDTH = 2048;

    private const WEBP_QUALITY = 85;

    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Optimize an uploaded image: convert to WebP and resize if too wide.
     *
     * @param  string  $path  The file path relative to the disk root
     * @param  string|null  $disk  The filesystem disk name (defaults to configured media disk)
     * @return string The resulting file path (may have changed extension to .webp)
     */
    public function optimize(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk();

        if (! $this->isOptimizable($path)) {
            return $path;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            Log::warning('ImageOptimizationService: file not found', ['path' => $path, 'disk' => $disk]);

            return $path;
        }

        try {
            $image = $this->manager->read($storage->get($path));

            if ($image->width() > self::MAX_WIDTH) {
                $image->scaleDown(width: self::MAX_WIDTH);
            }

            $webpContent = $image->toWebp(quality: self::WEBP_QUALITY)->toString();

            $newPath = $this->replaceExtension($path, 'webp');

            $storage->put($newPath, $webpContent);

            if ($newPath !== $path) {
                $storage->delete($path);
            }

            return $newPath;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizationService: optimization failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return $path;
        }
    }

    /**
     * Optimize a file uploaded via Filament FileUpload.
     * Alias for optimize() to clarify intent in calling code.
     *
     * @param  string  $path  The permanent file path after Filament saves the upload
     * @param  string|null  $disk  The filesystem disk name
     * @return string The resulting file path
     */
    public function optimizeFromUpload(string $path, ?string $disk = null): string
    {
        return $this->optimize($path, $disk);
    }

    /**
     * Create a thumbnail version alongside the original file.
     *
     * @param  string  $path  The file path relative to the disk root
     * @param  int  $width  Thumbnail width in pixels
     * @param  int  $height  Thumbnail height in pixels
     * @param  string|null  $disk  The filesystem disk name
     * @return string The thumbnail file path
     */
    public function createThumbnail(string $path, int $width = 400, int $height = 300, ?string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk();
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            Log::warning('ImageOptimizationService: file not found for thumbnail', ['path' => $path, 'disk' => $disk]);

            return $path;
        }

        try {
            $image = $this->manager->read($storage->get($path));

            $image->coverDown($width, $height);

            $webpContent = $image->toWebp(quality: self::WEBP_QUALITY)->toString();

            $thumbPath = $this->buildThumbnailPath($path);

            $storage->put($thumbPath, $webpContent);

            return $thumbPath;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizationService: thumbnail creation failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return $path;
        }
    }

    /**
     * Check whether a file extension is eligible for optimization.
     */
    public function isOptimizable(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::OPTIMIZABLE_EXTENSIONS, true);
    }

    /**
     * Replace the file extension in a path.
     */
    protected function replaceExtension(string $path, string $newExtension): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $base = $directory !== '.' ? $directory.'/' : '';

        return $base.$filename.'.'.$newExtension;
    }

    /**
     * Build a thumbnail path from the original file path.
     * Result: {directory}/{filename}_thumb.webp
     */
    protected function buildThumbnailPath(string $path): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $base = $directory !== '.' ? $directory.'/' : '';

        return $base.$filename.'_thumb.webp';
    }

    /**
     * Resolve the default disk name, respecting the admin-configured media disk.
     */
    protected function defaultDisk(): string
    {
        return config('filament.default_filesystem_disk', 'public');
    }
}
