{{-- Replaced wholesale by PromocodeController / AjaxCartController. --}}
<div class="js_summary_discount">
    @if($cart->discount_amount > 0)
        <div class="summary-item">
            <span class="summary-label">{{ t('shop.subtotal') }}</span>
            <span class="summary-value">{{ t('shop.currency') }}{{ $cart->subtotal_amount }}</span>
        </div>
        <div class="summary-item discount">
            <span class="summary-label">{{ t('shop.discount') }} ({{ $cart->promocode_code }})</span>
            <span class="summary-value">-{{ t('shop.currency') }}{{ $cart->discount_amount }}</span>
        </div>
    @endif
</div>
