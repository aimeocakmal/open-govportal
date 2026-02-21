<?php

namespace Database\Seeders;

use App\Models\Feedback;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        Feedback::factory()->create([
            'name' => 'Ahmad bin Ismail',
            'email' => 'ahmad@example.com',
            'subject' => 'Cadangan penambahbaikan laman web',
            'message' => 'Laman web ini sangat berguna. Saya cadangkan untuk menambah ciri carian yang lebih baik.',
            'page_url' => '/ms',
            'rating' => 4,
            'status' => 'new',
            'ip_address' => '203.0.113.1',
        ]);

        Feedback::factory()->read()->create([
            'name' => 'Sarah Lee',
            'email' => 'sarah@example.com',
            'subject' => 'Broken link on policy page',
            'message' => 'The download link for the ICT policy document returns a 404 error.',
            'page_url' => '/en/dasar',
            'rating' => 2,
            'ip_address' => '203.0.113.2',
        ]);

        Feedback::factory()->replied()->create([
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh@example.com',
            'subject' => 'Directory information outdated',
            'message' => 'Some of the staff directory entries appear to be outdated. Could you update them?',
            'page_url' => '/en/direktori',
            'rating' => 3,
            'reply' => 'Thank you for your feedback. We have updated the directory information.',
            'ip_address' => '203.0.113.3',
        ]);
    }
}
