<?php

namespace Tests\Feature\Filament\Dashboard;

use App\Filament\Widgets\NeedsAttentionWidget;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The widget is a list of things an admin still has to do. With an empty list
 * there is nothing to say, so the whole block — heading included — stays off
 * the dashboard.
 */
class NeedsAttentionWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_heading_is_hidden_when_nothing_needs_attention(): void
    {
        Livewire::test(NeedsAttentionWidget::class)
            ->assertSuccessful()
            ->assertDontSee(__('admin.dashboard.widgets.needs_attention'));
    }

    public function test_heading_appears_as_soon_as_one_counter_is_non_zero(): void
    {
        // Letters belong to the always-on Notification module, so this case
        // holds whichever modules config/modules.php has switched off.
        LogLetter::factory()->failed()->create();

        Livewire::test(NeedsAttentionWidget::class)
            ->assertSuccessful()
            ->assertSee(__('admin.dashboard.widgets.needs_attention'))
            ->assertSee(__('admin.dashboard.stats.failed_letters'));
    }

    public function test_sent_letters_alone_do_not_bring_the_block_back(): void
    {
        LogLetter::factory()->count(3)->create();

        Livewire::test(NeedsAttentionWidget::class)
            ->assertSuccessful()
            ->assertDontSee(__('admin.dashboard.widgets.needs_attention'));
    }

    /**
     * An empty widget would still take a grid row of its own on the page, so
     * the dashboard has to leave the block out entirely rather than render an
     * invisible one.
     */
    public function test_dashboard_leaves_the_block_out_entirely_when_nothing_is_pending(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertDontSee(__('admin.dashboard.widgets.needs_attention'));
        $response->assertDontSee(NeedsAttentionWidget::class, false);
    }

    public function test_dashboard_mounts_the_block_once_something_is_pending(): void
    {
        LogLetter::factory()->failed()->create();

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee(NeedsAttentionWidget::class, false);
    }
}
