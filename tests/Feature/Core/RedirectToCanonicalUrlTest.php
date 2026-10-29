<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Http\Middleware\RedirectToCanonicalUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class RedirectToCanonicalUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();

        config(['app.canonical_url' => 'https://nosi.com.ua']);
    }

    public function test_http_is_redirected_to_https(): void
    {
        $this->get('http://nosi.com.ua/dostavka')
            ->assertStatus(301)
            ->assertRedirect('https://nosi.com.ua/dostavka');
    }

    public function test_www_is_redirected_to_the_bare_domain_keeping_the_query(): void
    {
        $this->get('https://www.nosi.com.ua/shop?page=2')
            ->assertStatus(301)
            ->assertRedirect('https://nosi.com.ua/shop?page=2');
    }

    public function test_trailing_slash_is_removed(): void
    {
        // The test client trims a trailing slash itself, so the middleware gets a raw request.
        $response = (new RedirectToCanonicalUrl)->handle(
            Request::create('https://nosi.com.ua/shop/?sort=price_asc'),
            fn () => new Response('page'),
        );

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('https://nosi.com.ua/shop?sort=price_asc', $response->headers->get('Location'));
    }

    public function test_index_php_and_public_prefixes_are_removed(): void
    {
        $this->get('https://nosi.com.ua/index.php')->assertRedirect('https://nosi.com.ua');
        $this->get('https://nosi.com.ua/public/shop')->assertRedirect('https://nosi.com.ua/shop');
        $this->get('https://nosi.com.ua/public/index.php')->assertRedirect('https://nosi.com.ua');
    }

    public function test_the_canonical_address_is_served(): void
    {
        $this->get('https://nosi.com.ua/')->assertStatus(200);
    }

    public function test_a_path_starting_with_public_is_not_mistaken_for_the_prefix(): void
    {
        $response = $this->get('https://nosi.com.ua/publications');

        $this->assertNotSame(301, $response->getStatusCode());
    }

    public function test_post_requests_are_not_redirected(): void
    {
        $response = $this->post('http://www.nosi.com.ua/api/nothing-here');

        $this->assertNotSame(301, $response->getStatusCode());
    }

    public function test_nothing_happens_without_a_canonical_url(): void
    {
        config(['app.canonical_url' => null]);

        $response = $this->get('http://www.nosi.com.ua/');

        $response->assertStatus(200);
    }
}
