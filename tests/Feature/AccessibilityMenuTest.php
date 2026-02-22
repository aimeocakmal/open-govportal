<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_accessibility_icon_ms(): void
    {
        $this->get('/ms/')
            ->assertOk()
            ->assertSee(__('accessibility.open', [], 'ms'));
    }

    public function test_homepage_renders_accessibility_icon_en(): void
    {
        $this->get('/en/')
            ->assertOk()
            ->assertSee(__('accessibility.open', [], 'en'));
    }

    public function test_accessibility_menu_has_font_size_section_ms(): void
    {
        $response = $this->get('/ms/');

        $response->assertSee(__('accessibility.font_size', [], 'ms'));
        $response->assertSee(__('accessibility.small', [], 'ms'));
        $response->assertSee(__('accessibility.large', [], 'ms'));
        $response->assertSee(__('accessibility.extra_large', [], 'ms'));
    }

    public function test_accessibility_menu_has_font_type_section_ms(): void
    {
        $response = $this->get('/ms/');

        $response->assertSee(__('accessibility.font_type', [], 'ms'));
        $response->assertSee('Arial');
        $response->assertSee('Times New Roman');
        $response->assertSee('Courier New');
    }

    public function test_accessibility_menu_has_bg_color_section_ms(): void
    {
        $response = $this->get('/ms/');

        $response->assertSee(__('accessibility.bg_color', [], 'ms'));
        $response->assertSee(__('accessibility.white', [], 'ms'));
        $response->assertSee(__('accessibility.yellow', [], 'ms'));
        $response->assertSee(__('accessibility.blue', [], 'ms'));
    }

    public function test_accessibility_menu_has_contrast_section_ms(): void
    {
        $response = $this->get('/ms/');

        $response->assertSee(__('accessibility.contrast', [], 'ms'));
        $response->assertSee(__('accessibility.light_mode', [], 'ms'));
        $response->assertSee(__('accessibility.dark_mode', [], 'ms'));
    }

    public function test_accessibility_menu_has_reset_button_ms(): void
    {
        $this->get('/ms/')
            ->assertSee(__('accessibility.reset', [], 'ms'));
    }

    public function test_accessibility_menu_has_reset_button_en(): void
    {
        $this->get('/en/')
            ->assertSee(__('accessibility.reset', [], 'en'));
    }

    public function test_accessibility_menu_has_translated_title_ms(): void
    {
        $this->get('/ms/')
            ->assertSee('Menu Kebolehcapaian');
    }

    public function test_accessibility_menu_has_translated_title_en(): void
    {
        $this->get('/en/')
            ->assertSee('Accessibility Menu');
    }

    public function test_flash_prevention_script_is_present(): void
    {
        $response = $this->get('/ms/');
        $content = $response->getContent();

        $this->assertStringContainsString('a11y-fontsize', $content);
        $this->assertStringContainsString('a11y-fonttype', $content);
        $this->assertStringContainsString('a11y-bgcolor', $content);
        $this->assertStringContainsString('a11y-contrast', $content);
        $this->assertStringContainsString('localStorage', $content);
    }

    public function test_accessibility_menu_has_keyboard_shortcut(): void
    {
        $content = $this->get('/ms/')->getContent();

        $this->assertStringContainsString('keydown.window.ctrl.u.prevent', $content);
    }

    public function test_accessibility_menu_has_aria_attributes(): void
    {
        $content = $this->get('/ms/')->getContent();

        $this->assertStringContainsString('role="dialog"', $content);
        $this->assertStringContainsString('role="radiogroup"', $content);
        $this->assertStringContainsString('role="radio"', $content);
        $this->assertStringContainsString('aria-controls="a11y-panel"', $content);
    }
}
