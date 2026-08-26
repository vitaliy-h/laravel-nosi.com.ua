<?php

namespace Tests\Feature\Subscriber;

use App\Modules\Subscriber\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_store_with_valid_email_creates_subscriber_and_redirects_back(): void
    {
        $response = $this->from('/')->post('/subscriber', [
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('subscribers', ['email' => 'john@example.com']);
    }

    public function test_store_via_ajax_returns_actions_json(): void
    {
        // abc-response.js sends the XHR header, which is what $request->ajax() detects.
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post('/subscriber', [
                'email' => 'ajax@example.com',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('actions.0.method', 'clearForm');
        $response->assertJsonPath('actions.0.selector', '#newsletter-form');
        $response->assertJsonPath('actions.1.method', 'html');
        $response->assertJsonPath('actions.1.selector', '#newsletter-message');
        $this->assertDatabaseHas('subscribers', ['email' => 'ajax@example.com']);
    }

    public function test_store_with_invalid_email_redirects_back_with_validation_error(): void
    {
        $response = $this->from('/')->post('/subscriber', [
            'email' => 'not-an-email',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(0, Subscriber::count());
    }

    public function test_store_with_existing_email_does_not_duplicate(): void
    {
        $existing = Subscriber::factory()->integrated()->create(['email' => 'twice@example.com']);

        $response = $this->from('/')->post('/subscriber', [
            'email' => 'twice@example.com',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasNoErrors();
        $this->assertSame(1, Subscriber::where('email', 'twice@example.com')->count());
        // The existing CRM integration state must survive a repeated subscription.
        $this->assertNotNull($existing->fresh()->integrated_at);
    }

    public function test_flash_message_differs_for_new_and_repeated_email(): void
    {
        // The message now comes from the ActionResult envelope, not from the controller.
        $first = $this->from('/')->post('/subscriber', ['email' => 'msg@example.com']);
        $first->assertSessionHas('success', t('subscribe.saved'));

        $second = $this->from('/')->post('/subscriber', ['email' => 'msg@example.com']);
        $second->assertSessionHas('success', t('subscribe.already'));
    }

    public function test_store_creates_subscriber_not_integrated_yet(): void
    {
        $this->from('/')->post('/subscriber', [
            'email' => 'fresh@example.com',
        ]);

        $subscriber = Subscriber::where('email', 'fresh@example.com')->firstOrFail();

        $this->assertNull($subscriber->integrated_at);
        $this->assertNull($subscriber->error);
    }

    public function test_guest_can_subscribe(): void
    {
        $this->assertGuest();

        $response = $this->from('/')->post('/subscriber', [
            'email' => 'guest@example.com',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('subscribers', ['email' => 'guest@example.com']);
    }
}
