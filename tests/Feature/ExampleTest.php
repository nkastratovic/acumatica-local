<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_health_check_returns_a_successful_response(): void
    {
        $this->get('/up')->assertStatus(200);
    }

    public function test_the_home_page_requires_authentication(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
