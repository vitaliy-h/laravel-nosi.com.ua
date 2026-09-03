{{-- Replaced wholesale by PromocodeController (abc-response `replaceWith`). --}}
<div class="js_promocode_block promocode-form mb-3">
    @if($cart->promocode_code)
        <form data-abcr="{{ save_route('promocode.remove') }}" action="{{ save_route('promocode.remove') }}" method="POST">
            @csrf
            <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
            <div class="input-group">
                <span class="form-control">{{ $cart->promocode_code }}</span>
                <button class="btn btn-outline-danger" type="submit">{{ t('shop.promocode_remove') }}</button>
            </div>
        </form>
    @else
        <form data-abcr="{{ save_route('promocode.apply') }}" action="{{ save_route('promocode.apply') }}" method="POST">
            @csrf
            <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
            <div class="input-group">
                <input type="text" name="code" class="form-control" placeholder="{{ t('shop.promocode_placeholder') }}">
                <button class="btn btn-accent" type="submit">{{ t('shop.promocode_apply') }}</button>
            </div>
        </form>
    @endif

    @if(!empty($result?->message))
        <div class="promocode-message small mt-2 {{ $result->success ? 'text-success' : 'text-danger' }}">
            {{ $result->message }}
        </div>
    @endif
</div>
