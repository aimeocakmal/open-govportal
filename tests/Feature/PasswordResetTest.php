<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_password_reset_page_is_accessible(): void
    {
        $this->get('/admin/password-reset/request')
            ->assertOk();
    }

    public function test_login_page_shows_forgot_password_link(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('/admin/password-reset/request');
    }

    public function test_password_reset_request_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['is_active' => true]);

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_reset_request_does_not_reveal_non_existent_email(): void
    {
        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'nonexistent@example.com'])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $oldHash = $user->password;

        $token = Password::broker()->createToken($user);

        Livewire::test(ResetPassword::class, [
            'email' => $user->email,
            'token' => $token,
        ])
            ->fillForm([
                'password' => 'new-secure-password',
                'passwordConfirmation' => 'new-secure-password',
            ])
            ->call('resetPassword');

        $user->refresh();
        $this->assertNotEquals($oldHash, $user->password);
    }
}
