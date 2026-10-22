<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Shop\Categories\CategoryResource;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_is_rendered_in_admin_locale(): void
    {
        config(['app.admin_locale' => 'uk']);

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Увійдіть у свій акаунт');
    }

    public function test_admin_locale_can_be_switched_to_english(): void
    {
        config(['app.admin_locale' => 'en']);

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Sign in');
    }

    public function test_resource_and_enum_labels_are_translated(): void
    {
        app()->setLocale('uk');

        $this->assertSame('категорію', CategoryResource::getModelLabel());
        $this->assertSame('Категорії', CategoryResource::getPluralModelLabel());
        $this->assertSame('Оплачено', PaymentStatusEnum::PAID->label());
    }
}
