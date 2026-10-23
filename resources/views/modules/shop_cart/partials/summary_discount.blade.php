{{-- Replaced wholesale by PromocodeController / AjaxCartController. --}}
<div class="js_summary_discount">
    @if($cart->has_discount)
        <div class="summary-row">
            <span>{{ t('shop.subtotal') }}</span>
            <span>{{ $cart->subtotal_amount_formatted }}</span>
        </div>
        <div class="summary-row discount">
            <span>{{ t('shop.discount') }} ({{ $cart->promocode_code }})</span>
            <span>−{{ $cart->discount_amount_formatted }}</span>
        </div>
    @endif
</div>
