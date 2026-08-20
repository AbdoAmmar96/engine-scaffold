<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // The site pages query the catalog tables, so the in-memory
    // test database (phpunit.xml) needs the migrations run.
    use RefreshDatabase;

    /**
     * The root path redirects to the default locale — see routes/web.php:7.
     */
    public function test_the_root_redirects_to_the_default_locale(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/ar');
    }

    public function test_the_arabic_home_page_returns_a_successful_response(): void
    {
        $response = $this->get('/ar');

        $response->assertStatus(200);
    }
}
