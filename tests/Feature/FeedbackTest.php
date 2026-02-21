<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_feedback_via_factory(): void
    {
        $feedback = Feedback::factory()->create();

        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
            'message' => $feedback->message,
        ]);
    }

    public function test_new_scope_returns_only_new_records(): void
    {
        Feedback::factory()->create(['status' => 'new']);
        Feedback::factory()->create(['status' => 'new']);
        Feedback::factory()->read()->create();
        Feedback::factory()->replied()->create();

        $new = Feedback::new()->get();

        $this->assertCount(2, $new);
    }

    public function test_unread_scope_returns_only_unread_records(): void
    {
        Feedback::factory()->create(['status' => 'new']);
        Feedback::factory()->read()->create();
        Feedback::factory()->archived()->create();

        $unread = Feedback::unread()->get();

        $this->assertCount(1, $unread);
    }

    public function test_replier_relationship(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->replied()->create(['replied_by' => $user->id]);

        $this->assertInstanceOf(User::class, $feedback->replier);
        $this->assertEquals($user->id, $feedback->replier->id);
    }

    public function test_rating_is_cast_to_integer(): void
    {
        $feedback = Feedback::factory()->create(['rating' => 4]);

        $this->assertIsInt($feedback->rating);
        $this->assertEquals(4, $feedback->rating);
    }

    public function test_replied_at_is_cast_to_datetime(): void
    {
        $feedback = Feedback::factory()->replied()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $feedback->replied_at);
    }

    public function test_factory_states_set_correct_status(): void
    {
        $read = Feedback::factory()->read()->create();
        $replied = Feedback::factory()->replied()->create();
        $archived = Feedback::factory()->archived()->create();

        $this->assertEquals('read', $read->status);
        $this->assertEquals('replied', $replied->status);
        $this->assertEquals('archived', $archived->status);
    }

    public function test_uses_feedbacks_table(): void
    {
        $feedback = Feedback::factory()->create();

        $this->assertEquals('feedbacks', $feedback->getTable());
    }
}
