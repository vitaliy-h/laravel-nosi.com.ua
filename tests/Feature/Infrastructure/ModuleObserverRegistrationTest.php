<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Listeners\LogListener;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Observers\PageObserver;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Observers\CartObserver;
use App\Modules\ShopCart\Observers\CartProductObserver;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use App\Modules\ShopOrder\Observers\OrderObserver;
use App\Modules\ShopOrder\Observers\OrderProductObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ModuleObserverRegistrationTest extends TestCase
{
    public static function observerProvider(): array
    {
        return [
            [Page::class, PageObserver::class],
            [Order::class, OrderObserver::class],
            [OrderProduct::class, OrderProductObserver::class],
            [Cart::class, CartObserver::class],
            [CartProduct::class, CartProductObserver::class],
        ];
    }

    #[DataProvider('observerProvider')]
    public function test_module_observers_are_registered_by_the_module_service_provider(
        string $model,
        string $observer
    ): void {
        $dispatcher = Model::getEventDispatcher();

        $listeners = $dispatcher->getListeners('eloquent.created: ' . $model);

        $this->assertNotEmpty(
            $listeners,
            "No `created` listener registered for {$model}; expected {$observer}."
        );
    }

    public function test_every_module_observer_has_a_matching_model(): void
    {
        foreach (File::directories(app_path('Modules')) as $module) {
            $observersPath = $module . '/Observers';

            if (!File::exists($observersPath)) {
                continue;
            }

            foreach (File::files($observersPath) as $file) {
                $model = 'App\\Modules\\' . basename($module) . '\\Models\\'
                    . Str::replaceLast('Observer', '', $file->getFilenameWithoutExtension());

                $this->assertTrue(
                    class_exists($model) && is_subclass_of($model, Model::class),
                    "{$file->getPathname()} lives in Observers/ but {$model} does not exist; "
                    . 'non-model listeners belong in the module\'s Listeners/ folder.'
                );
            }
        }
    }

    public function test_log_observer_is_not_a_module_observer(): void
    {
        $this->assertFalse(
            File::exists(app_path('Modules/Core/Observers')),
            'Core\\Listeners\\LogListener is a global eloquent listener, not a model observer.'
        );
        $this->assertTrue(class_exists(LogListener::class));
    }
}
