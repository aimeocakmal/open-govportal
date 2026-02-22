<?php

namespace App\Livewire;

use App\Models\Feedback;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $submitted = false;

    public bool $rateLimited = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('hubungi.form.name')]),
            'email.required' => __('validation.required', ['attribute' => __('hubungi.form.email')]),
            'email.email' => __('validation.email', ['attribute' => __('hubungi.form.email')]),
            'subject.required' => __('validation.required', ['attribute' => __('hubungi.form.subject')]),
            'message.required' => __('validation.required', ['attribute' => __('hubungi.form.message')]),
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $ip = request()->ip();
        $rateLimitKey = "contact-form:{$ip}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $this->rateLimited = true;

            return;
        }

        RateLimiter::hit($rateLimitKey, 3600);

        Feedback::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'page_url' => url()->previous(),
            'status' => 'new',
            'ip_address' => $ip,
        ]);

        $this->reset(['name', 'email', 'subject', 'message']);
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.contact-form');
    }
}
