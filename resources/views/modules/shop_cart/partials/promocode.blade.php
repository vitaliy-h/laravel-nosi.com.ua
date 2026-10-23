{{-- Replaced wholesale by PromocodeController (abc-response `replaceWith`). --}}
<div class="js_promocode_block promocode">
    @if($cart->promocode_code)
        <form data-abcr="{{ save_route('promocode.remove') }}" action="{{ save_route('promocode.remove') }}" method="POST">
            @csrf
            <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
            <div class="input-group">
                <span class="input" style="display: flex; align-items: center;">{{ $cart->promocode_code }}</span>
                <button class="btn btn-line btn-sm" type="submit">{{ t('shop.promocode_remove') }}</button>
            </div>
        </form>
    @else
        <form data-abcr="{{ save_route('promocode.apply') }}" action="{{ save_route('promocode.apply') }}" method="POST">
            @csrf
            <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
            <div class="input-group">
                <input type="text" name="code" class="input" placeholder="{{ t('shop.promocode_placeholder') }}" aria-label="{{ t('shop.promocode') }}">
                <button class="btn btn-dark btn-sm" type="submit">{{ t('shop.promocode_apply') }}</button>
            </div>
        </form>
    @endif

    @if(!empty($result?->message))
        <div class="promocode-message {{ $result->success ? 'ok' : 'fail' }}">{{ $result->message }}</div>
    @endif
</div>
