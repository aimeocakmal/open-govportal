<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class MediaDiskService
{
    /**
     * Resolve the active media disk from settings, configure it, and set it as the Filament default.
     */
    public function apply(): void
    {
        $disk = $this->getActiveDiskName();

        match ($disk) {
            's3' => $this->configureS3(),
            'r2' => $this->configureR2(),
            'gcs' => $this->configureGcs(),
            'azure' => $this->configureAzure(),
            default => null, // 'public' needs no extra config
        };

        Config::set('filament.default_filesystem_disk', $disk);
    }

    /**
     * Read the admin-configured media disk name and map 'local' to the web-accessible 'public' disk.
     */
    public function getActiveDiskName(): string
    {
        try {
            $disk = Setting::get('media_disk', 'local');
        } catch (\Throwable) {
            return 'public';
        }

        if ($disk === 'local' || $disk === null || $disk === '') {
            return 'public';
        }

        return $disk;
    }

    /**
     * Populate the S3 disk config from admin settings.
     */
    private function configureS3(): void
    {
        Config::set('filesystems.disks.s3', [
            'driver' => 's3',
            'key' => $this->decryptSetting('media_s3_key'),
            'secret' => $this->decryptSetting('media_s3_secret'),
            'region' => Setting::get('media_s3_region', 'ap-southeast-1'),
            'bucket' => Setting::get('media_s3_bucket', ''),
            'url' => Setting::get('media_s3_url', ''),
            'endpoint' => Setting::get('media_s3_endpoint', ''),
            'use_path_style_endpoint' => false,
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]);
    }

    /**
     * Populate the R2 disk config from admin settings.
     * R2 is S3-compatible; endpoint is derived from the account ID.
     */
    private function configureR2(): void
    {
        $accountId = Setting::get('media_r2_account_id', '');
        $endpoint = $accountId !== '' ? "https://{$accountId}.r2.cloudflarestorage.com" : '';

        Config::set('filesystems.disks.r2', [
            'driver' => 's3',
            'key' => $this->decryptSetting('media_r2_access_key'),
            'secret' => $this->decryptSetting('media_r2_secret_key'),
            'region' => 'auto',
            'bucket' => Setting::get('media_r2_bucket', ''),
            'url' => Setting::get('media_r2_public_url', ''),
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => false,
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]);
    }

    /**
     * Populate the GCS disk config from admin settings.
     * The key_json setting is a JSON string that must be decoded into an array.
     */
    private function configureGcs(): void
    {
        $keyJson = $this->decryptSetting('media_gcs_key_json');
        $keyFile = $keyJson !== '' ? json_decode($keyJson, true) : [];

        Config::set('filesystems.disks.gcs', [
            'driver' => 'gcs',
            'project_id' => Setting::get('media_gcs_project_id', ''),
            'key_file' => is_array($keyFile) ? $keyFile : [],
            'bucket' => Setting::get('media_gcs_bucket', ''),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]);
    }

    /**
     * Populate the Azure Blob Storage disk config from admin settings.
     */
    private function configureAzure(): void
    {
        Config::set('filesystems.disks.azure', [
            'driver' => 'azure',
            'account' => Setting::get('media_azure_account', ''),
            'key' => $this->decryptSetting('media_azure_key'),
            'container' => Setting::get('media_azure_container', ''),
            'url' => Setting::get('media_azure_url', ''),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]);
    }

    /**
     * Decrypt an encrypted setting value, returning empty string on failure.
     */
    private function decryptSetting(string $key): string
    {
        $raw = Setting::get($key, '');

        if ($raw === '' || $raw === null) {
            return '';
        }

        try {
            return Crypt::decrypt($raw);
        } catch (DecryptException) {
            return '';
        }
    }
}
