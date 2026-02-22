<?php

namespace Tests\Feature;

use App\Models\StaffDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirektoriPageTest extends TestCase
{
    use RefreshDatabase;

    // ── Listing page routes ──────────────────────────────────────────

    public function test_direktori_returns_ok_in_ms(): void
    {
        $response = $this->get('/ms/direktori');

        $response->assertOk();
    }

    public function test_direktori_returns_ok_in_en(): void
    {
        $response = $this->get('/en/direktori');

        $response->assertOk();
    }

    public function test_direktori_shows_page_title_in_ms(): void
    {
        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('Direktori Kakitangan');
    }

    public function test_direktori_shows_page_title_in_en(): void
    {
        $response = $this->get('/en/direktori');

        $response->assertOk();
        $response->assertSee('Staff Directory');
    }

    public function test_direktori_shows_description_in_ms(): void
    {
        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('Direktori kakitangan Kementerian Digital Malaysia.');
    }

    public function test_direktori_shows_description_in_en(): void
    {
        $response = $this->get('/en/direktori');

        $response->assertOk();
        $response->assertSee('Staff directory of the Ministry of Digital Malaysia.');
    }

    public function test_direktori_has_breadcrumb(): void
    {
        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('Laman Utama');
        $response->assertSee('Direktori Kakitangan');
    }

    // ── Livewire DirectoriSearch component ────────────────────────────

    public function test_direktori_shows_active_staff(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Ahmad bin Ali',
            'is_active' => true,
        ]);

        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('Ahmad bin Ali');
    }

    public function test_direktori_hides_inactive_staff(): void
    {
        StaffDirectory::factory()->inactive()->create([
            'name' => 'Kakitangan Tidak Aktif',
        ]);

        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertDontSee('Kakitangan Tidak Aktif');
    }

    public function test_direktori_shows_staff_in_en_locale(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Siti Aminah',
            'position_ms' => 'Pengarah',
            'position_en' => 'Director',
            'department_ms' => 'Bahagian Teknologi',
            'department_en' => 'Technology Division',
        ]);

        $response = $this->get('/en/direktori');

        $response->assertOk();
        $response->assertSee('Siti Aminah');
        $response->assertSee('Director');
        $response->assertSee('Technology Division');
    }

    public function test_direktori_shows_no_results_message(): void
    {
        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('Tiada kakitangan dijumpai.');
    }

    public function test_direktori_shows_no_results_message_in_en(): void
    {
        $response = $this->get('/en/direktori');

        $response->assertOk();
        $response->assertSee('No staff found.');
    }

    public function test_direktori_search_filters_by_name(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Ahmad bin Ali',
        ]);
        StaffDirectory::factory()->create([
            'name' => 'Siti binti Hassan',
        ]);

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->set('query', 'Ahmad')
            ->assertSee('Ahmad bin Ali')
            ->assertDontSee('Siti binti Hassan');
    }

    public function test_direktori_search_filters_by_position(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Pengarah Staff',
            'position_ms' => 'Pengarah Kanan',
        ]);
        StaffDirectory::factory()->create([
            'name' => 'Pembantu Staff',
            'position_ms' => 'Pembantu Tadbir',
        ]);

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->set('query', 'Pengarah')
            ->assertSee('Pengarah Staff')
            ->assertDontSee('Pembantu Staff');
    }

    public function test_direktori_search_filters_by_department(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Jabatan Teknologi Staff',
            'department_ms' => 'Bahagian Teknologi',
        ]);
        StaffDirectory::factory()->create([
            'name' => 'Jabatan Operasi Staff',
            'department_ms' => 'Bahagian Operasi',
        ]);

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->set('query', 'Teknologi')
            ->assertSee('Jabatan Teknologi Staff')
            ->assertDontSee('Jabatan Operasi Staff');
    }

    public function test_direktori_filters_by_department_dropdown(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Teknologi Person',
            'department_ms' => 'Bahagian Teknologi',
        ]);
        StaffDirectory::factory()->create([
            'name' => 'Operasi Person',
            'department_ms' => 'Bahagian Operasi',
        ]);

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->set('jabatan', 'Bahagian Teknologi')
            ->assertSee('Teknologi Person')
            ->assertDontSee('Operasi Person');
    }

    public function test_direktori_paginates_at_12_per_page(): void
    {
        StaffDirectory::factory()->count(15)->create();

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->assertViewHas('staff', function ($staff) {
                return $staff->perPage() === 12;
            });
    }

    public function test_direktori_resets_page_on_query_change(): void
    {
        StaffDirectory::factory()->count(15)->create();

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->set('query', 'test')
            ->assertSet('paginators.page', 1);
    }

    public function test_direktori_resets_page_on_department_change(): void
    {
        StaffDirectory::factory()->count(15)->create();

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->set('jabatan', 'Bahagian Teknologi')
            ->assertSet('paginators.page', 1);
    }

    public function test_direktori_department_dropdown_populated(): void
    {
        StaffDirectory::factory()->create([
            'department_ms' => 'Bahagian Teknologi',
        ]);
        StaffDirectory::factory()->create([
            'department_ms' => 'Bahagian Operasi',
        ]);

        Livewire::test(\App\Livewire\DirektoriSearch::class)
            ->assertViewHas('departments', function ($departments) {
                return $departments->contains('Bahagian Teknologi')
                    && $departments->contains('Bahagian Operasi');
            });
    }

    public function test_direktori_shows_staff_email(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Email Staff',
            'email' => 'staff@digital.gov.my',
        ]);

        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('staff@digital.gov.my');
    }

    public function test_direktori_shows_staff_phone(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Phone Staff',
            'phone' => '+603-8000-1234',
        ]);

        $response = $this->get('/ms/direktori');

        $response->assertOk();
        $response->assertSee('+603-8000-1234');
    }
}
