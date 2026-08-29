{{-- Replaced wholesale by PromocodeController / AjaxCartController. --}}
<div class="js_summary_discount">
    @if($cart->has_discount)
        <div class="summary-item">
            <span class="summary-label">{{ t('shop.subtotal') }}</span>
            <span class="summary-value">{{ $cart->subtotal_amount_formatted }}</span>
        </div>
        <div class="summary-item discount">
            <span class="summary-label">{{ t('shop.discount') }} ({{ $cart->promocode_code }})</span>
            <span class="summary-value">-{{ $cart->discount_amount_formatted }}</span>
        </div>
    @endif
</div>
