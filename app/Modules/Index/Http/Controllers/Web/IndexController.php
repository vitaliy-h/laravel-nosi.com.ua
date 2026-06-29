<?php

namespace App\Modules\Index\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Repository\ShopRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(private readonly ShopRepository $shopRepository)
    {
    }

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        $bestSellers = $this->shopRepository->bestSellers(4);
        $featuredCategories = $this->shopRepository->featuredCategories(4);
        $featuredProducts = $this->shopRepository->featuredProducts(8);
        $specialCategories = $this->shopRepository->specialCategories(12);

        return view('pages.index', compact('bestSellers', 'featuredCategories', 'featuredProducts', 'specialCategories'));
    }
}
