<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\MediaDiskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class MediaDiskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_to_public_disk_when_no_setting(): void
    {
        $service = new MediaDiskService;

        $this->assertEquals('public', $service->getActiveDiskName());
    }

    public function test_maps_local_to_public_disk(): void
    {
        Setting::set('media_disk', 'local');

        $service = new MediaDiskService;

        $this->assertEquals('public', $service->getActiveDiskName());
    }

    public function test_resolves_s3_disk(): void
    {
        Setting::set('media_disk', 's3');

        $service = new MediaDiskService;

        $this->assertEquals('s3', $service->getActiveDiskName());
    }

    public function test_resolves_r2_disk(): void
    {
        Setting::set('media_disk', 'r2');

        $service = new MediaDiskService;

        $this->assertEquals('r2', $service->getActiveDiskName());
    }

    public function test_apply_sets_filament_default_disk_for_local(): void
    {
        $service = new MediaDiskService;
        $service->apply();

        $this->assertEquals('public', config('filament.default_filesystem_disk'));
    }

    public function test_apply_populates_s3_config_from_settings(): void
    {
        Setting::set('media_disk', 's3');
        Setting::set('media_s3_key', Crypt::encrypt('test-key'));
        Setting::set('media_s3_secret', Crypt::encrypt('test-secret'));
        Setting::set('media_s3_region', 'us-east-1');
        Setting::set('media_s3_bucket', 'my-bucket');
        Setting::set('media_s3_url', 'https://cdn.example.com');
        Setting::set('media_s3_endpoint', 'https://s3.example.com');

        $service = new MediaDiskService;
        $service->apply();

        $this->assertEquals('s3', config('filament.default_filesystem_disk'));
        $this->assertEquals('test-key', config('filesystems.disks.s3.key'));
        $this->assertEquals('test-secret', config('filesystems.disks.s3.secret'));
        $this->assertEquals('us-east-1', config('filesystems.disks.s3.region'));
        $this->assertEquals('my-bucket', config('filesystems.disks.s3.bucket'));
        $this->assertEquals('https://cdn.example.com', config('filesystems.disks.s3.url'));
        $this->assertEquals('https://s3.example.com', config('filesystems.disks.s3.endpoint'));
        $this->assertEquals('public', config('filesystems.disks.s3.visibility'));
    }

    public function test_apply_populates_r2_config_with_endpoint(): void
    {
        Setting::set('media_disk', 'r2');
        Setting::set('media_r2_access_key', Crypt::encrypt('r2-key'));
        Setting::set('media_r2_secret_key', Crypt::encrypt('r2-secret'));
        Setting::set('media_r2_account_id', 'abc123');
        Setting::set('media_r2_bucket', 'r2-bucket');
        Setting::set('media_r2_public_url', 'https://media.example.com');

        $service = new MediaDiskService;
        $service->apply();

        $this->assertEquals('r2', config('filament.default_filesystem_disk'));
        $this->assertEquals('r2-key', config('filesystems.disks.r2.key'));
        $this->assertEquals('r2-secret', config('filesystems.disks.r2.secret'));
        $this->assertEquals('auto', config('filesystems.disks.r2.region'));
        $this->assertEquals('r2-bucket', config('filesystems.disks.r2.bucket'));
        $this->assertEquals('https://media.example.com', config('filesystems.disks.r2.url'));
        $this->assertEquals('https://abc123.r2.cloudflarestorage.com', config('filesystems.disks.r2.endpoint'));
        $this->assertEquals('s3', config('filesystems.disks.r2.driver'));
    }

    public function test_apply_populates_gcs_config_from_settings(): void
    {
        $keyFileJson = json_encode(['type' => 'service_account', 'project_id' => 'my-project']);

        Setting::set('media_disk', 'gcs');
        Setting::set('media_gcs_project_id', 'my-project');
        Setting::set('media_gcs_bucket', 'gcs-bucket');
        Setting::set('media_gcs_key_json', Crypt::encrypt($keyFileJson));

        $service = new MediaDiskService;
        $service->apply();

        $this->assertEquals('gcs', config('filament.default_filesystem_disk'));
        $this->assertEquals('my-project', config('filesystems.disks.gcs.project_id'));
        $this->assertEquals('gcs-bucket', config('filesystems.disks.gcs.bucket'));
        $this->assertEquals(['type' => 'service_account', 'project_id' => 'my-project'], config('filesystems.disks.gcs.key_file'));
    }

    public function test_apply_populates_azure_config_from_settings(): void
    {
        Setting::set('media_disk', 'azure');
        Setting::set('media_azure_account', 'myaccount');
        Setting::set('media_azure_key', Crypt::encrypt('azure-key'));
        Setting::set('media_azure_container', 'media');
        Setting::set('media_azure_url', 'https://media.example.com');

        $service = new MediaDiskService;
        $service->apply();

        $this->assertEquals('azure', config('filament.default_filesystem_disk'));
        $this->assertEquals('myaccount', config('filesystems.disks.azure.account'));
        $this->assertEquals('azure-key', config('filesystems.disks.azure.key'));
        $this->assertEquals('media', config('filesystems.disks.azure.container'));
        $this->assertEquals('https://media.example.com', config('filesystems.disks.azure.url'));
    }

    public function test_graceful_fallback_when_settings_table_missing(): void
    {
        // Drop the settings table to simulate a fresh install
        \Illuminate\Support\Facades\Schema::dropIfExists('settings');

        $service = new MediaDiskService;

        $this->assertEquals('public', $service->getActiveDiskName());
    }

    public function test_corrupted_encrypted_value_returns_empty_string(): void
    {
        // Store a non-encrypted value in an encrypted field
        Setting::set('media_disk', 's3');
        Setting::set('media_s3_key', 'not-encrypted-value');
        Setting::set('media_s3_secret', 'also-not-encrypted');
        Setting::set('media_s3_region', 'us-east-1');
        Setting::set('media_s3_bucket', 'my-bucket');

        $service = new MediaDiskService;
        $service->apply();

        // Corrupted encrypted values should return empty string, not throw
        $this->assertEquals('', config('filesystems.disks.s3.key'));
        $this->assertEquals('', config('filesystems.disks.s3.secret'));
        // Non-encrypted values should be returned normally
        $this->assertEquals('us-east-1', config('filesystems.disks.s3.region'));
        $this->assertEquals('my-bucket', config('filesystems.disks.s3.bucket'));
    }

    public function test_empty_encrypted_value_returns_empty_string(): void
    {
        Setting::set('media_disk', 's3');
        Setting::set('media_s3_key', '');
        Setting::set('media_s3_secret', '');
        Setting::set('media_s3_region', 'us-east-1');
        Setting::set('media_s3_bucket', 'my-bucket');

        $service = new MediaDiskService;
        $service->apply();

        $this->assertEquals('', config('filesystems.disks.s3.key'));
        $this->assertEquals('', config('filesystems.disks.s3.secret'));
    }

    public function test_maps_empty_string_disk_to_public(): void
    {
        Setting::set('media_disk', '');

        $service = new MediaDiskService;

        $this->assertEquals('public', $service->getActiveDiskName());
    }
}
