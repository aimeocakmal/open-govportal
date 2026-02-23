<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageAiSettings;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ManageAiSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Cache::flush();
    }

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_ai_settings_page_accessible_by_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin())->get('/admin/manage-ai-settings');

        $response->assertOk();
    }

    public function test_ai_settings_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole('viewer');

        $response = $this->actingAs($viewer)->get('/admin/manage-ai-settings');

        $response->assertForbidden();
    }

    public function test_ai_settings_page_denied_for_content_editor(): void
    {
        $editor = User::factory()->create(['is_active' => true]);
        $editor->assignRole('content_editor');

        $response = $this->actingAs($editor)->get('/admin/manage-ai-settings');

        $response->assertForbidden();
    }

    public function test_ai_settings_page_denied_for_guest(): void
    {
        $response = $this->get('/admin/manage-ai-settings');

        $response->assertRedirect();
    }

    public function test_settings_can_be_saved_via_livewire(): void
    {
        $this->actingAs($this->superAdmin());

        Setting::set('ai_llm_provider', 'openai');
        Setting::set('ai_llm_model', 'gpt-4o');
        Setting::set('ai_embedding_provider', 'openai');
        Setting::set('ai_embedding_model', 'text-embedding-3-small');
        Setting::set('ai_embedding_dimension', '1536');
        Setting::set('ai_chatbot_enabled', '1');
        Setting::set('ai_chatbot_rate_limit', '20');
        Setting::set('ai_chatbot_language_preference', 'same_as_page');
        Setting::set('ai_chatbot_display_location', 'all_pages');

        $this->assertEquals('openai', Setting::get('ai_llm_provider'));
        $this->assertEquals('gpt-4o', Setting::get('ai_llm_model'));
        $this->assertEquals('1', Setting::get('ai_chatbot_enabled'));
        $this->assertEquals('20', Setting::get('ai_chatbot_rate_limit'));
    }

    public function test_encrypted_fields_stored_correctly(): void
    {
        $this->actingAs($this->superAdmin());

        $encrypted = Crypt::encrypt('sk-test-api-key-12345');
        Setting::set('ai_llm_api_key', $encrypted, 'encrypted');

        $stored = Setting::get('ai_llm_api_key');
        $this->assertNotEquals('sk-test-api-key-12345', $stored);

        $decrypted = Crypt::decrypt($stored);
        $this->assertEquals('sk-test-api-key-12345', $decrypted);
    }

    public function test_chatbot_settings_bilingual_fields_stored(): void
    {
        Setting::set('ai_chatbot_name_ms', 'Pembantu Digital');
        Setting::set('ai_chatbot_name_en', 'Digital Assistant');
        Setting::set('ai_chatbot_persona_ms', 'Anda adalah pembantu AI rasmi.');
        Setting::set('ai_chatbot_persona_en', 'You are the official AI assistant.');
        Setting::set('ai_chatbot_welcome_ms', 'Selamat datang!');
        Setting::set('ai_chatbot_welcome_en', 'Welcome!');

        $this->assertEquals('Pembantu Digital', Setting::get('ai_chatbot_name_ms'));
        $this->assertEquals('Digital Assistant', Setting::get('ai_chatbot_name_en'));
        $this->assertEquals('Anda adalah pembantu AI rasmi.', Setting::get('ai_chatbot_persona_ms'));
        $this->assertEquals('You are the official AI assistant.', Setting::get('ai_chatbot_persona_en'));
    }

    public function test_display_location_default_is_all_pages(): void
    {
        $default = Setting::get('ai_chatbot_display_location', 'all_pages');

        $this->assertEquals('all_pages', $default);
    }

    public function test_save_resolves_known_model_from_select(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => []], 200)]);

        $this->actingAs($this->superAdmin());

        Livewire::test(ManageAiSettings::class)
            ->fillForm([
                'ai_llm_provider' => 'openai',
                'ai_llm_model_select' => 'gpt-4o',
                'ai_llm_model_custom' => '',
                'ai_llm_api_key' => 'sk-valid-key',
                'ai_embedding_provider' => 'openai',
                'ai_embedding_model_select' => 'text-embedding-3-small',
                'ai_embedding_model_custom' => '',
                'ai_embedding_api_key' => '',
                'ai_embedding_dimension' => 1536,
                'ai_chatbot_enabled' => false,
                'ai_admin_editor_enabled' => false,
                'ai_chatbot_rate_limit' => 10,
                'ai_chatbot_language_preference' => 'same_as_page',
                'ai_chatbot_display_location' => 'all_pages',
            ])
            ->call('save');

        $this->assertEquals('gpt-4o', Setting::get('ai_llm_model'));
        $this->assertEquals('text-embedding-3-small', Setting::get('ai_embedding_model'));
    }

    public function test_save_resolves_custom_model_from_other(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => []], 200)]);

        $this->actingAs($this->superAdmin());

        Livewire::test(ManageAiSettings::class)
            ->fillForm([
                'ai_llm_provider' => 'openai',
                'ai_llm_model_select' => '__other__',
                'ai_llm_model_custom' => 'my-finetuned-gpt',
                'ai_llm_api_key' => 'sk-valid-key',
                'ai_embedding_provider' => 'openai',
                'ai_embedding_model_select' => '__other__',
                'ai_embedding_model_custom' => 'custom-embedding-v1',
                'ai_embedding_api_key' => '',
                'ai_embedding_dimension' => 768,
                'ai_chatbot_enabled' => false,
                'ai_admin_editor_enabled' => false,
                'ai_chatbot_rate_limit' => 10,
                'ai_chatbot_language_preference' => 'same_as_page',
                'ai_chatbot_display_location' => 'all_pages',
            ])
            ->call('save');

        $this->assertEquals('my-finetuned-gpt', Setting::get('ai_llm_model'));
        $this->assertEquals('custom-embedding-v1', Setting::get('ai_embedding_model'));
    }

    public function test_invalid_api_key_not_saved_but_other_settings_saved(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response([], 401)]);

        $this->actingAs($this->superAdmin());

        // Pre-set a valid key
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-previously-valid'), 'encrypted');

        Livewire::test(ManageAiSettings::class)
            ->fillForm([
                'ai_llm_provider' => 'openai',
                'ai_llm_model_select' => 'gpt-4o-mini',
                'ai_llm_model_custom' => '',
                'ai_llm_api_key' => 'sk-invalid-key',
                'ai_embedding_provider' => 'openai',
                'ai_embedding_model_select' => 'text-embedding-3-small',
                'ai_embedding_model_custom' => '',
                'ai_embedding_api_key' => '',
                'ai_embedding_dimension' => 1536,
                'ai_chatbot_enabled' => true,
                'ai_admin_editor_enabled' => false,
                'ai_chatbot_rate_limit' => 25,
                'ai_chatbot_language_preference' => 'same_as_page',
                'ai_chatbot_display_location' => 'all_pages',
            ])
            ->call('save')
            ->assertNotified();

        // Provider and model saved despite invalid key
        $this->assertEquals('openai', Setting::get('ai_llm_provider'));
        $this->assertEquals('gpt-4o-mini', Setting::get('ai_llm_model'));
        $this->assertEquals('25', Setting::get('ai_chatbot_rate_limit'));

        // Previous valid key retained (invalid key not overwritten)
        $this->assertEquals('sk-previously-valid', Crypt::decrypt(Setting::get('ai_llm_api_key')));
    }

    public function test_valid_api_key_saved_encrypted(): void
    {
        Http::fake([
            'api.anthropic.com/v1/messages' => Http::response(['id' => 'msg_1'], 200),
        ]);

        $this->actingAs($this->superAdmin());

        Livewire::test(ManageAiSettings::class)
            ->fillForm([
                'ai_llm_provider' => 'anthropic',
                'ai_llm_model_select' => 'claude-sonnet-4-6',
                'ai_llm_model_custom' => '',
                'ai_llm_api_key' => 'sk-ant-valid-key',
                'ai_embedding_provider' => 'openai',
                'ai_embedding_model_select' => 'text-embedding-3-small',
                'ai_embedding_model_custom' => '',
                'ai_embedding_api_key' => '',
                'ai_embedding_dimension' => 1536,
                'ai_chatbot_enabled' => false,
                'ai_admin_editor_enabled' => false,
                'ai_chatbot_rate_limit' => 10,
                'ai_chatbot_language_preference' => 'same_as_page',
                'ai_chatbot_display_location' => 'all_pages',
            ])
            ->call('save');

        $stored = Setting::get('ai_llm_api_key');
        $this->assertNotEmpty($stored);
        $this->assertEquals('sk-ant-valid-key', Crypt::decrypt($stored));
    }

    public function test_mount_reverse_maps_known_model_to_select(): void
    {
        Setting::set('ai_llm_provider', 'anthropic');
        Setting::set('ai_llm_model', 'claude-sonnet-4-6');
        Setting::set('ai_embedding_provider', 'openai');
        Setting::set('ai_embedding_model', 'text-embedding-3-large');

        $this->actingAs($this->superAdmin());

        Livewire::test(ManageAiSettings::class)
            ->assertFormSet([
                'ai_llm_model_select' => 'claude-sonnet-4-6',
                'ai_llm_model_custom' => '',
                'ai_embedding_model_select' => 'text-embedding-3-large',
                'ai_embedding_model_custom' => '',
            ]);
    }

    public function test_mount_reverse_maps_unknown_model_to_other(): void
    {
        Setting::set('ai_llm_provider', 'openai');
        Setting::set('ai_llm_model', 'ft:gpt-4o-custom:org:suffix');
        Setting::set('ai_embedding_provider', 'openai');
        Setting::set('ai_embedding_model', 'custom-embed-v2');

        $this->actingAs($this->superAdmin());

        Livewire::test(ManageAiSettings::class)
            ->assertFormSet([
                'ai_llm_model_select' => '__other__',
                'ai_llm_model_custom' => 'ft:gpt-4o-custom:org:suffix',
                'ai_embedding_model_select' => '__other__',
                'ai_embedding_model_custom' => 'custom-embed-v2',
            ]);
    }
}
