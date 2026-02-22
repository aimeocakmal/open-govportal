<?php

namespace Tests\Feature;

use App\Filament\Pages\ManagePlatformVersion;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformVersionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_platform_version_page_accessible_by_admin(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/admin/manage-platform-version');

        $response->assertOk();
    }

    public function test_platform_version_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole('viewer');

        $response = $this->actingAs($viewer)->get('/admin/manage-platform-version');

        $response->assertForbidden();
    }

    public function test_version_json_file_exists_and_is_valid(): void
    {
        $path = base_path('version.json');

        $this->assertFileExists($path);

        $data = json_decode(file_get_contents($path), true);

        $this->assertNotNull($data);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('changelog', $data);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $data['version']);
        $this->assertIsArray($data['changelog']);
    }

    public function test_read_version_file_returns_structured_data(): void
    {
        $data = ManagePlatformVersion::readVersionFile();

        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('released_at', $data);
        $this->assertArrayHasKey('changelog', $data);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $data['version']);
    }

    public function test_changelog_sections_have_valid_types(): void
    {
        $data = ManagePlatformVersion::readVersionFile();
        $validTypes = ['added', 'changed', 'fixed', 'removed'];

        foreach ($data['changelog'] as $section) {
            $this->assertArrayHasKey('type', $section);
            $this->assertArrayHasKey('items', $section);
            $this->assertContains($section['type'], $validTypes);
            $this->assertIsArray($section['items']);
            $this->assertNotEmpty($section['items']);
        }
    }

    public function test_page_displays_version_number(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        $data = ManagePlatformVersion::readVersionFile();

        $response = $this->actingAs($admin)->get('/admin/manage-platform-version');

        $response->assertOk();
        $response->assertSee('v'.$data['version']);
    }
}
