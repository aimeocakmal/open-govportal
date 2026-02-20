<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root URL redirects to a locale-prefixed homepage.
     * The actual locale depends on the Accept-Language header; either ms or en is valid.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Root redirects to /{locale} — follow the redirect and expect 200
        $response->assertRedirect();
        $this->followRedirects($response)->assertOk();
    }
}
