<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiUsageLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_usage_log(): void
    {
        $log = AiUsageLog::create([
            'operation' => 'grammar_check',
            'locale' => 'ms',
            'duration_ms' => 150,
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'provider' => 'anthropic',
            'model' => 'claude-sonnet-4-6',
        ]);

        $this->assertDatabaseHas('ai_usage_logs', [
            'id' => $log->id,
            'operation' => 'grammar_check',
            'locale' => 'ms',
            'provider' => 'anthropic',
        ]);
    }

    public function test_created_at_is_automatically_set(): void
    {
        $log = AiUsageLog::create([
            'operation' => 'translate',
            'locale' => 'en',
            'duration_ms' => 200,
            'provider' => 'openai',
            'model' => 'gpt-4o',
        ]);

        $this->assertNotNull($log->fresh()->created_at);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $log = AiUsageLog::create([
            'operation' => 'expand',
        ]);

        $log = $log->fresh();
        $this->assertNull($log->locale);
        $this->assertNull($log->duration_ms);
        $this->assertNull($log->prompt_tokens);
        $this->assertNull($log->completion_tokens);
        $this->assertNull($log->provider);
        $this->assertNull($log->model);
    }

    public function test_casts_return_correct_types(): void
    {
        $log = AiUsageLog::create([
            'operation' => 'summarise',
            'locale' => 'ms',
            'duration_ms' => 300,
            'prompt_tokens' => 200,
            'completion_tokens' => 100,
            'provider' => 'anthropic',
            'model' => 'claude-sonnet-4-6',
        ]);

        $log = $log->fresh();
        $this->assertIsInt($log->duration_ms);
        $this->assertIsInt($log->prompt_tokens);
        $this->assertIsInt($log->completion_tokens);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->created_at);
    }

    public function test_no_user_pii_columns_exist(): void
    {
        $log = AiUsageLog::create([
            'operation' => 'generate',
            'locale' => 'ms',
        ]);

        $attributes = array_keys($log->fresh()->getAttributes());

        $this->assertNotContains('user_id', $attributes);
        $this->assertNotContains('ip_address', $attributes);
        $this->assertNotContains('content', $attributes);
        $this->assertNotContains('email', $attributes);
    }

    public function test_multiple_logs_can_be_queried(): void
    {
        AiUsageLog::create(['operation' => 'grammar_check', 'locale' => 'ms']);
        AiUsageLog::create(['operation' => 'translate', 'locale' => 'en']);
        AiUsageLog::create(['operation' => 'expand', 'locale' => 'ms']);

        $this->assertEquals(3, AiUsageLog::count());
        $this->assertEquals(2, AiUsageLog::where('locale', 'ms')->count());
    }

    public function test_operation_values_are_stored_correctly(): void
    {
        $operations = ['grammar_check', 'translate', 'expand', 'summarise', 'tldr', 'write_excerpt', 'generate', 'chat', 'embed'];

        foreach ($operations as $op) {
            AiUsageLog::create(['operation' => $op]);
        }

        $this->assertEquals(count($operations), AiUsageLog::count());

        foreach ($operations as $op) {
            $this->assertDatabaseHas('ai_usage_logs', ['operation' => $op]);
        }
    }
}
