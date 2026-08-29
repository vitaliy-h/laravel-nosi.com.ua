<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\ViewModels\BreadcrumbViewModel;
use Tests\TestCase;

class BaseViewModelTest extends TestCase
{
    public function test_to_view_data_maps_public_properties_to_view_variables(): void
    {
        $vm = new BreadcrumbViewModel(title: 'Shop', url: '/shop');

        $this->assertSame(['title' => 'Shop', 'url' => '/shop'], $vm->toViewData());
    }

    /**
     * The whole point of toViewData() over toArray(): blade must receive the
     * nested ViewModel as an object, not as an array.
     */
    public function test_to_view_data_keeps_nested_view_models_as_objects(): void
    {
        $page = new BaseViewModelTestPage(
            crumb: new BreadcrumbViewModel(title: 'Shop', url: '/shop'),
        );

        $data = $page->toViewData();

        $this->assertInstanceOf(BreadcrumbViewModel::class, $data['crumb']);
        $this->assertIsArray($page->toArray()['crumb']);
    }

    public function test_to_view_data_exposes_no_internals_of_the_data_layer(): void
    {
        $data = (new BreadcrumbViewModel(title: 'Shop', url: '/shop'))->toViewData();

        foreach (array_keys($data) as $key) {
            $this->assertStringStartsNotWith('_', $key);
        }
    }
}

class BaseViewModelTestPage extends BaseViewModel
{
    public function __construct(
        public readonly BreadcrumbViewModel $crumb,
    ) {}
}
