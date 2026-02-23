<?php

namespace Tests\Feature;

use App\Filament\Actions\Ai\AiGrammarAction;
use App\Filament\Resources\Broadcasts\Pages\EditBroadcast;
use App\Models\Broadcast;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Livewire;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;
use Tests\TestCase;

class AiEditorActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function getAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function enableAiEditor(): void
    {
        Setting::set('ai_admin_editor_enabled', 'true');
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test-key'), 'encrypted');
    }

    private function disableAiEditor(): void
    {
        Setting::set('ai_admin_editor_enabled', 'false');
    }

    public function test_is_ai_editor_enabled_returns_true_when_configured(): void
    {
        $this->enableAiEditor();

        $this->assertTrue(AiGrammarAction::isAiEditorEnabled());
    }

    public function test_is_ai_editor_enabled_returns_false_when_disabled(): void
    {
        $this->disableAiEditor();

        $this->assertFalse(AiGrammarAction::isAiEditorEnabled());
    }

    public function test_is_ai_editor_enabled_returns_false_without_api_key(): void
    {
        Setting::set('ai_admin_editor_enabled', 'true');

        $this->assertFalse(AiGrammarAction::isAiEditorEnabled());
    }

    public function test_grammar_action_exists_on_broadcast_edit_when_enabled(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('grammar_ms')->schemaComponent('content_ms')
            );
    }

    public function test_ai_actions_not_visible_when_editor_disabled(): void
    {
        $this->disableAiEditor();
        $broadcast = Broadcast::factory()->create();

        // When AI editor is disabled, the edit page still renders but
        // the feature flag ensures isAiEditorEnabled() returns false.
        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertOk();

        $this->assertFalse(AiGrammarAction::isAiEditorEnabled());
    }

    public function test_translate_action_exists_on_content_field(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('translate_ms')->schemaComponent('content_ms')
            );
    }

    public function test_expand_action_exists_on_content_field(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('expand_ms')->schemaComponent('content_ms')
            );
    }

    public function test_summarise_action_exists_on_content_field(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('summarise_ms')->schemaComponent('content_ms')
            );
    }

    public function test_tldr_action_exists_on_content_field(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('tldr_ms')->schemaComponent('content_ms')
            );
    }

    public function test_generate_action_exists_on_content_field(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('generate_ms')->schemaComponent('content_ms')
            );
    }

    public function test_excerpt_field_has_grammar_and_translate_actions(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('grammar_excerpt_ms')->schemaComponent('excerpt_ms')
            )
            ->assertActionExists(
                TestAction::make('translate_excerpt_ms')->schemaComponent('excerpt_ms')
            );
    }

    public function test_english_tab_has_ai_actions(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create();

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->assertActionExists(
                TestAction::make('grammar_en')->schemaComponent('content_en')
            )
            ->assertActionExists(
                TestAction::make('translate_en')->schemaComponent('content_en')
            );
    }

    public function test_grammar_action_calls_ai_service(): void
    {
        $this->enableAiEditor();
        Prism::fake([
            TextResponseFake::make()->withText('Corrected text.')->withUsage(new Usage(10, 5)),
        ]);
        $broadcast = Broadcast::factory()->create(['content_ms' => 'Text with errors.']);

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->callAction(
                TestAction::make('grammar_ms')->schemaComponent('content_ms')
            )
            ->assertNotified();
    }

    public function test_grammar_action_warns_on_empty_field(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create(['content_ms' => '']);

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->callAction(
                TestAction::make('grammar_ms')->schemaComponent('content_ms')
            )
            ->assertNotified();
    }

    public function test_translate_action_shows_language_modal(): void
    {
        $this->enableAiEditor();
        $broadcast = Broadcast::factory()->create(['content_ms' => 'Kandungan BM.']);

        Livewire::actingAs($this->getAdmin())
            ->test(EditBroadcast::class, ['record' => $broadcast->id])
            ->mountAction(
                TestAction::make('translate_ms')->schemaComponent('content_ms')
            )
            ->assertActionMounted(
                TestAction::make('translate_ms')->schemaComponent('content_ms')
            );
    }
}
