<?php

namespace Tests\Feature\Filament\Feedback;

use App\Filament\Resources\Feedback\Pages\ManageFeedback;
use App\Modules\Feedback\Models\Feedback;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeedbackResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('feedback');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Feedback::factory()->count(3)->create();

        Livewire::test(ManageFeedback::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_name(): void
    {
        $target = Feedback::factory()->create(['name' => 'UniqueUserName']);
        $other = Feedback::factory()->create(['name' => 'OtherPerson']);

        Livewire::test(ManageFeedback::class)
            ->searchTable('UniqueUserName')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_email(): void
    {
        $target = Feedback::factory()->create(['email' => 'find@example.com']);
        $other = Feedback::factory()->create(['email' => 'other@example.com']);

        Livewire::test(ManageFeedback::class)
            ->searchTable('find@example.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_feedback_with_valid_data(): void
    {
        Livewire::test(ManageFeedback::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'    => 'Jane',
                'email'   => 'jane@example.com',
                'ip'      => '192.168.1.1',
                'message' => 'Hello world',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('feedback', ['email' => 'jane@example.com']);
    }

    public function test_create_feedback_requires_name(): void
    {
        Livewire::test(ManageFeedback::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'email'   => 'jane@example.com',
                'ip'      => '127.0.0.1',
                'message' => 'Hello',
            ])
            ->assertHasFormErrors(['name']);
    }

    public function test_create_feedback_requires_email(): void
    {
        Livewire::test(ManageFeedback::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'    => 'Jane',
                'ip'      => '127.0.0.1',
                'message' => 'Hello',
            ])
            ->assertHasFormErrors(['email']);
    }

    public function test_create_feedback_requires_message(): void
    {
        Livewire::test(ManageFeedback::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'  => 'Jane',
                'email' => 'jane@example.com',
                'ip'    => '127.0.0.1',
            ])
            ->assertHasFormErrors(['message']);
    }

    public function test_edit_feedback_with_valid_data(): void
    {
        $feedback = Feedback::factory()->create(['name' => 'OldName']);

        Livewire::test(ManageFeedback::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($feedback), data: [
                'name'    => 'UpdatedName',
                'email'   => $feedback->email,
                'ip'      => $feedback->ip,
                'message' => $feedback->message,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('feedback', ['id' => $feedback->id, 'name' => 'UpdatedName']);
    }

    public function test_delete_feedback(): void
    {
        $feedback = Feedback::factory()->create();

        Livewire::test(ManageFeedback::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($feedback))
            ->assertSuccessful();

        $this->assertModelMissing($feedback);
    }
}
