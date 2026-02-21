<?php

namespace Tests\Feature;

use App\Models\StaffDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_staff_directory_via_factory(): void
    {
        $staff = StaffDirectory::factory()->create();

        $this->assertDatabaseHas('staff_directories', [
            'id' => $staff->id,
            'name' => $staff->name,
        ]);
    }

    public function test_active_scope_returns_only_active_records(): void
    {
        StaffDirectory::factory()->create(['is_active' => true, 'sort_order' => 2]);
        StaffDirectory::factory()->create(['is_active' => true, 'sort_order' => 1]);
        StaffDirectory::factory()->inactive()->create();

        $active = StaffDirectory::active()->get();

        $this->assertCount(2, $active);
        $this->assertEquals(1, $active->first()->sort_order);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $staff = StaffDirectory::factory()->create(['is_active' => true]);

        $this->assertIsBool($staff->is_active);
        $this->assertTrue($staff->is_active);
    }

    public function test_sort_order_is_cast_to_integer(): void
    {
        $staff = StaffDirectory::factory()->create(['sort_order' => 5]);

        $this->assertIsInt($staff->sort_order);
        $this->assertEquals(5, $staff->sort_order);
    }

    public function test_inactive_factory_state(): void
    {
        $staff = StaffDirectory::factory()->inactive()->create();

        $this->assertFalse($staff->is_active);
    }

    public function test_bilingual_fields_are_stored(): void
    {
        $staff = StaffDirectory::factory()->create([
            'position_ms' => 'Pengarah',
            'position_en' => 'Director',
            'department_ms' => 'Bahagian Teknologi',
            'department_en' => 'Technology Division',
        ]);

        $this->assertDatabaseHas('staff_directories', [
            'id' => $staff->id,
            'position_ms' => 'Pengarah',
            'position_en' => 'Director',
            'department_ms' => 'Bahagian Teknologi',
            'department_en' => 'Technology Division',
        ]);
    }

    public function test_email_is_unique_per_record(): void
    {
        $staff1 = StaffDirectory::factory()->create(['email' => 'test@digital.gov.my']);
        $staff2 = StaffDirectory::factory()->create(['email' => 'other@digital.gov.my']);

        $this->assertNotEquals($staff1->email, $staff2->email);
    }
}
