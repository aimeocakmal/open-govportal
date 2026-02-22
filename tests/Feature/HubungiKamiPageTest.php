<?php

namespace Tests\Feature;

use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class HubungiKamiPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_hubungi_kami_page_returns_200_ms(): void
    {
        $this->get('/ms/hubungi-kami')->assertOk();
    }

    public function test_hubungi_kami_page_returns_200_en(): void
    {
        $this->get('/en/hubungi-kami')->assertOk();
    }

    public function test_hubungi_kami_page_displays_title_ms(): void
    {
        $this->get('/ms/hubungi-kami')->assertSee('Hubungi Kami');
    }

    public function test_hubungi_kami_page_displays_title_en(): void
    {
        $this->get('/en/hubungi-kami')->assertSee('Contact Us');
    }

    public function test_hubungi_kami_page_shows_addresses(): void
    {
        Address::factory()->create([
            'label_ms' => 'Ibu Pejabat',
            'is_active' => true,
        ]);

        $this->get('/ms/hubungi-kami')->assertSee('Ibu Pejabat');
    }

    public function test_hubungi_kami_page_hides_inactive_addresses(): void
    {
        Address::factory()->inactive()->create([
            'label_ms' => 'Old Office',
        ]);

        $this->get('/ms/hubungi-kami')->assertDontSee('Old Office');
    }

    public function test_hubungi_kami_page_shows_contact_form(): void
    {
        $this->get('/ms/hubungi-kami')->assertSee('Hantar Maklum Balas');
    }

    public function test_contact_form_requires_name(): void
    {
        Livewire::test(\App\Livewire\ContactForm::class)
            ->set('email', 'test@example.com')
            ->set('subject', 'Test')
            ->set('message', 'Test message')
            ->call('submit')
            ->assertHasErrors(['name']);
    }

    public function test_contact_form_requires_valid_email(): void
    {
        Livewire::test(\App\Livewire\ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'not-an-email')
            ->set('subject', 'Test')
            ->set('message', 'Test message')
            ->call('submit')
            ->assertHasErrors(['email']);
    }

    public function test_contact_form_requires_subject(): void
    {
        Livewire::test(\App\Livewire\ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'test@example.com')
            ->set('message', 'Test message')
            ->call('submit')
            ->assertHasErrors(['subject']);
    }

    public function test_contact_form_requires_message(): void
    {
        Livewire::test(\App\Livewire\ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'test@example.com')
            ->set('subject', 'Test')
            ->call('submit')
            ->assertHasErrors(['message']);
    }

    public function test_contact_form_submits_successfully(): void
    {
        Livewire::test(\App\Livewire\ContactForm::class)
            ->set('name', 'Ahmad bin Ali')
            ->set('email', 'ahmad@example.com')
            ->set('subject', 'Pertanyaan')
            ->set('message', 'Saya ingin bertanya tentang perkhidmatan.')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('subject', '')
            ->assertSet('message', '');

        $this->assertDatabaseHas('feedbacks', [
            'name' => 'Ahmad bin Ali',
            'email' => 'ahmad@example.com',
            'subject' => 'Pertanyaan',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_rate_limiting(): void
    {
        $ip = '127.0.0.1';
        $key = "contact-form:{$ip}";

        // Simulate 5 previous submissions
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 3600);
        }

        Livewire::test(\App\Livewire\ContactForm::class)
            ->set('name', 'Test')
            ->set('email', 'test@example.com')
            ->set('subject', 'Test')
            ->set('message', 'Test message')
            ->call('submit')
            ->assertSet('rateLimited', true);

        $this->assertDatabaseMissing('feedbacks', ['name' => 'Test']);
    }

    public function test_hubungi_kami_page_has_breadcrumb(): void
    {
        $this->get('/ms/hubungi-kami')->assertSee('Laman Utama');
    }
}
