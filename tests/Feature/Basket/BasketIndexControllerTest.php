<?php

namespace Tests\Feature\Basket;

use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasketIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPagesForRouting();
    }

    public function test_index_shows_empty_basket_view_without_session(): void
    {
        $response = $this->get('/basket');

        $response->assertStatus(200);
        $response->assertViewIs('pages.basket.empty_basket');
    }

    public function test_index_redirects_to_uuid_url_when_basket_has_items(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $response = $this->withSession(['basket_uuid' => $basket->uuid])->get('/basket');

        $response->assertRedirect('/basket/'.$basket->uuid);
    }

    public function test_index_by_uuid_shows_basket_view_with_items(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $response = $this->get('/basket/'.$basket->uuid);

        $response->assertStatus(200);
        $response->assertViewIs('pages.basket.index');
    }

    public function test_index_by_uuid_shows_empty_view_when_no_items(): void
    {
        $basket = Basket::factory()->create();

        $response = $this->get('/basket/'.$basket->uuid);

        $response->assertStatus(200);
        $response->assertViewIs('pages.basket.empty_basket');
    }

    public function test_index_by_unknown_uuid_returns_404(): void
    {
        $response = $this->get('/basket/'.\Illuminate\Support\Str::uuid());

        $response->assertStatus(404);
    }

    public function test_checkout_segment_renders_checkout_view(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $response = $this->withSession(['basket_uuid' => $basket->uuid])->get('/basket/checkout');

        $response->assertStatus(200);
        $response->assertViewIs('pages.basket.checkout');
    }
}
