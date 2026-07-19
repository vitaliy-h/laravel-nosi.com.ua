<?php

namespace Tests\Feature\Feedback;

use App\Modules\Feedback\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_index_returns_200(): void
    {
        $response = $this->get('/feedback');

        $response->assertStatus(200);
        $response->assertViewIs('pages.feedback');
    }

    public function test_store_with_valid_data_creates_feedback_and_redirects_back(): void
    {
        $this->seedLetterTemplates();

        $response = $this->from('/feedback')->post('/feedback', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+380671234567',
            'message' => 'Hello there',
        ]);

        $response->assertRedirect('/feedback');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('feedback', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+380671234567',
            'message' => 'Hello there',
        ]);
    }

    public function test_store_with_missing_fields_redirects_back_with_validation_errors(): void
    {
        $response = $this->from('/feedback')->post('/feedback', []);

        $response->assertRedirect('/feedback');
        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertSame(0, Feedback::count());
    }

    public function test_store_with_invalid_email_redirects_back_with_validation_error(): void
    {
        $response = $this->from('/feedback')->post('/feedback', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'message' => 'Hello there',
        ]);

        $response->assertRedirect('/feedback');
        $response->assertSessionHasErrors(['email']);
    }
}
