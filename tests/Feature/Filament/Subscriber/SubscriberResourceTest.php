<?php

namespace Tests\Feature\Filament\Subscriber;

use App\Filament\Resources\Subscribers\Pages\ManageSubscribers;
use App\Modules\Core\Models\User;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Subscriber\Models\Subscriber;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('subscribers');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Subscriber::factory()->count(3)->create();

        Livewire::test(ManageSubscribers::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_email(): void
    {
        $target = Subscriber::factory()->create(['email' => 'find@example.com']);
        $other = Subscriber::factory()->create(['email' => 'other@example.com']);

        Livewire::test(ManageSubscribers::class)
            ->searchTable('find@example.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_integrated_at(): void
    {
        $integrated = Subscriber::factory()->integrated()->create();
        $pending = Subscriber::factory()->create();

        Livewire::test(ManageSubscribers::class)
            ->filterTable('integrated_at', true)
            ->assertCanSeeTableRecords([$integrated])
            ->assertCanNotSeeTableRecords([$pending]);

        Livewire::test(ManageSubscribers::class)
            ->filterTable('integrated_at', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$integrated]);
    }

    public function test_create_subscriber_with_valid_data(): void
    {
        Livewire::test(ManageSubscribers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'email' => 'jane@example.com',
                'ip' => '192.168.1.1',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('subscribers', ['email' => 'jane@example.com']);
    }

    public function test_create_subscriber_requires_email(): void
    {
        Livewire::test(ManageSubscribers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'ip' => '127.0.0.1',
            ])
            ->assertHasFormErrors(['email']);
    }

    public function test_edit_subscriber_with_valid_data(): void
    {
        $subscriber = Subscriber::factory()->create(['email' => 'old@example.com']);

        Livewire::test(ManageSubscribers::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($subscriber), data: [
                'email' => 'updated@example.com',
                'ip' => $subscriber->ip,
                'error' => 'CRM timeout',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'email' => 'updated@example.com',
            'error' => 'CRM timeout',
        ]);
    }

    public function test_delete_subscriber(): void
    {
        $subscriber = Subscriber::factory()->create();

        Livewire::test(ManageSubscribers::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($subscriber))
            ->assertSuccessful();

        $this->assertModelMissing($subscriber);
    }
}
