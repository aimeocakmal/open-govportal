<?php

namespace Tests\Feature;

use App\Jobs\OptimizeImageJob;
use App\Services\ImageOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class ImageOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImageOptimizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImageOptimizationService;
        Storage::fake('public');
    }

    public function test_is_optimizable_returns_true_for_jpeg(): void
    {
        $this->assertTrue($this->service->isOptimizable('photo.jpeg'));
        $this->assertTrue($this->service->isOptimizable('photo.jpg'));
        $this->assertTrue($this->service->isOptimizable('photo.JPG'));
    }

    public function test_is_optimizable_returns_true_for_png(): void
    {
        $this->assertTrue($this->service->isOptimizable('image.png'));
    }

    public function test_is_optimizable_returns_false_for_svg(): void
    {
        $this->assertFalse($this->service->isOptimizable('logo.svg'));
    }

    public function test_is_optimizable_returns_false_for_webp(): void
    {
        $this->assertFalse($this->service->isOptimizable('already.webp'));
    }

    public function test_is_optimizable_returns_false_for_pdf(): void
    {
        $this->assertFalse($this->service->isOptimizable('document.pdf'));
    }

    public function test_optimize_converts_jpeg_to_webp(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->create(100, 100)->fill('ff0000');
        $jpegContent = $image->toJpeg()->toString();

        Storage::disk('public')->put('uploads/test.jpg', $jpegContent);

        $result = $this->service->optimize('uploads/test.jpg', 'public');

        $this->assertEquals('uploads/test.webp', $result);
        Storage::disk('public')->assertExists('uploads/test.webp');
        Storage::disk('public')->assertMissing('uploads/test.jpg');
    }

    public function test_optimize_skips_svg_files(): void
    {
        Storage::disk('public')->put('uploads/logo.svg', '<svg></svg>');

        $result = $this->service->optimize('uploads/logo.svg', 'public');

        $this->assertEquals('uploads/logo.svg', $result);
        Storage::disk('public')->assertExists('uploads/logo.svg');
    }

    public function test_optimize_skips_webp_files(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->create(50, 50)->fill('00ff00');
        $webpContent = $image->toWebp()->toString();

        Storage::disk('public')->put('uploads/already.webp', $webpContent);

        $result = $this->service->optimize('uploads/already.webp', 'public');

        $this->assertEquals('uploads/already.webp', $result);
    }

    public function test_optimize_resizes_large_images(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->create(2100, 100)->fill('0000ff');
        $jpegContent = $image->toJpeg()->toString();

        Storage::disk('public')->put('uploads/large.jpg', $jpegContent);

        $result = $this->service->optimize('uploads/large.jpg', 'public');

        $this->assertEquals('uploads/large.webp', $result);
        Storage::disk('public')->assertExists('uploads/large.webp');

        // Verify the image was resized
        $optimized = $manager->read(Storage::disk('public')->get($result));
        $this->assertLessThanOrEqual(2048, $optimized->width());
    }

    public function test_create_thumbnail_generates_thumb_file(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->create(200, 150)->fill('ff00ff');
        $jpegContent = $image->toJpeg()->toString();

        Storage::disk('public')->put('uploads/photo.jpg', $jpegContent);

        $thumbPath = $this->service->createThumbnail('uploads/photo.jpg', 400, 300, 'public');

        $this->assertEquals('uploads/photo_thumb.webp', $thumbPath);
        Storage::disk('public')->assertExists('uploads/photo_thumb.webp');

        // Original should still exist
        Storage::disk('public')->assertExists('uploads/photo.jpg');
    }

    public function test_optimize_returns_original_path_for_missing_file(): void
    {
        $result = $this->service->optimize('nonexistent/file.jpg', 'public');

        $this->assertEquals('nonexistent/file.jpg', $result);
    }

    public function test_optimize_image_job_dispatches(): void
    {
        Queue::fake();

        OptimizeImageJob::dispatch('uploads/test.jpg', 'public');

        Queue::assertPushed(OptimizeImageJob::class, function ($job) {
            return $job->path === 'uploads/test.jpg' && $job->disk === 'public';
        });
    }

    public function test_optimize_image_job_with_thumbnail(): void
    {
        Queue::fake();

        OptimizeImageJob::dispatch('uploads/test.jpg', 'public', true);

        Queue::assertPushed(OptimizeImageJob::class, function ($job) {
            return $job->path === 'uploads/test.jpg' && $job->createThumbnail === true;
        });
    }
}
