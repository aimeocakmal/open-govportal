<?php

namespace Tests\Feature;

use App\Models\MinisterProfile;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilKementerianPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_page_returns_200_ms(): void
    {
        $this->get('/ms/profil-kementerian')->assertOk();
    }

    public function test_profil_page_returns_200_en(): void
    {
        $this->get('/en/profil-kementerian')->assertOk();
    }

    public function test_profil_page_displays_title_ms(): void
    {
        $this->get('/ms/profil-kementerian')->assertSee('Profil Kementerian');
    }

    public function test_profil_page_displays_title_en(): void
    {
        $this->get('/en/profil-kementerian')->assertSee('Ministry Profile');
    }

    public function test_profil_page_shows_current_minister(): void
    {
        MinisterProfile::factory()->create([
            'name' => 'Nurul Hidayah binti Azman',
            'is_current' => true,
        ]);

        $this->get('/ms/profil-kementerian')->assertSee('Nurul Hidayah binti Azman');
    }

    public function test_profil_page_hides_former_minister(): void
    {
        MinisterProfile::factory()->former()->create([
            'name' => 'Former Minister Name',
        ]);

        $this->get('/ms/profil-kementerian')->assertDontSee('Former Minister Name');
    }

    public function test_profil_page_shows_minister_title_in_locale(): void
    {
        MinisterProfile::factory()->create([
            'title_ms' => 'Ketua Pegawai Digital',
            'title_en' => 'Chief Digital Officer',
            'is_current' => true,
        ]);

        $this->get('/ms/profil-kementerian')->assertSee('Ketua Pegawai Digital');
        $this->get('/en/profil-kementerian')->assertSee('Chief Digital Officer');
    }

    public function test_profil_page_shows_vision(): void
    {
        Setting::set('vision_ms', '<p>Visi Kementerian</p>');

        $this->get('/ms/profil-kementerian')->assertSee('Visi Kementerian');
    }

    public function test_profil_page_shows_mission(): void
    {
        Setting::set('mission_en', '<p>Ministry Mission</p>');

        $this->get('/en/profil-kementerian')->assertSee('Ministry Mission');
    }

    public function test_profil_page_shows_no_minister_message(): void
    {
        $this->get('/ms/profil-kementerian')->assertSee('Tiada profil ketua organisasi');
    }

    public function test_profil_page_has_breadcrumb(): void
    {
        $this->get('/ms/profil-kementerian')->assertSee('Laman Utama');
    }
}
