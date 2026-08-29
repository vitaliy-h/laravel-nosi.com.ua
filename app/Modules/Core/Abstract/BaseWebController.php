<?php

namespace App\Modules\Core\Abstract;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

abstract class BaseWebController extends Controller
{
    /**
     * Single entry point for blade views in web controllers —
     * don't call the view() helper directly.
     */
    protected function render(string $view, array $data = [], array $mergeData = []): View
    {
        /** @var Factory $factory */
        $factory = view();

        return $factory->make($view, $data, $mergeData);
    }
}
