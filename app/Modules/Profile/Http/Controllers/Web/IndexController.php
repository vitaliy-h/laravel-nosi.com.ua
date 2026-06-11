<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Profile\Dtos\OrderFilterData;
use App\Modules\Profile\Repository\OrderRepository;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(private readonly OrderRepository $orderRepository)
    {
    }


    public function accessCheck(): Response|RedirectResponse|null
    {
        if (!auth()->check()) {
            return redirect()->to(module_url('auth'));
        }
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        if (empty($segments)) {
            return redirect()->to(module_url('profile', 'edit'));
        }

        $segment = $segments[0];
        $id = $segments[1] ?? null;

        if (method_exists($this, $segment)) {
            return $this->$segment($request, $id);
        }

        abort(404);
    }

    public function edit(Request $request, ?string $id = null)
    {
        $user = auth()->user();
        $activeSection = 'edit';
        return view('pages.profile.edit', compact('user', 'activeSection'));
    }

    public function orders(Request $request, ?string $id = null)
    {
        $user = auth()->user();
        $activeSection = 'orders';

        if ($id !== null) {
            $order = $this->orderRepository->findForUser((int)$id, $user->id);
            return view('pages.profile.order', compact('user', 'activeSection', 'order'));
        }

        $filter = new OrderFilterData(
            search: $request->query('search') ?: null,
            status: $request->query('status') ? PaymentStatusEnum::tryFrom($request->query('status')) : null,
        );
        $orders = $this->orderRepository->forUser($user->id, $filter);
        return view('pages.profile.orders', compact('user', 'activeSection', 'orders', 'filter'));
    }
}
