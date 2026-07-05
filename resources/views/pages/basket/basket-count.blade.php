@php
    if (!Session::has('basket_count')) {
        $basket = app(\App\Modules\Basket\Actions\FindBasketAction::class)->handle(Auth::user());
        Session::put('basket_count', $basket?->items()->sum('quantity') ?? 0);
    }
@endphp

<a href="{{module_url('basket')}}" class="header-action-btn">
    <i class="bi bi-cart3"></i>
    <span class="badge js_basket_count">{{ Session::get('basket_count') }}</span>
</a>
