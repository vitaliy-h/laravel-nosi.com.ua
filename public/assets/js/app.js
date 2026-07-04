/**
 * Project-specific behavior on top of the /demo template.
 * Keep /demo/assets/js/main.js untouched (vendor template) — add all our
 * customizations here so template updates don't wipe out our changes.
 */
(function () {
  'use strict';

  // Template's own decrease/increase/remove-item listeners bind directly to
  // buttons on DOMContentLoaded and don't dispatch a `change` event, so cart
  // forms never auto-submit. Strip them (clone trick removes all bound
  // listeners) and replace with delegated handlers below.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quantity-btn, .remove-item').forEach(function (el) {
      el.replaceWith(el.cloneNode(true));
    });
  });

  // Quantity +/- buttons: update the input and fire `change` so the cart
  // form (data-abcr) picks it up and submits via abc-response.js.
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.quantity-btn');
    if (!btn) return;

    const wrap = btn.closest('.quantity-selector');
    const input = wrap ? wrap.querySelector('.quantity-input') : null;
    if (!input) return;

    let count = parseInt(input.value, 10) || 1;

    if (btn.classList.contains('increase')) count++;
    if (btn.classList.contains('decrease')) count--;
    if (count < 1) count = 1;

    input.value = count;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // Auto-submit the cart quantity form (in the mini-cart modal and the
  // basket page) whenever the quantity input changes.
  document.addEventListener('change', function (e) {
    if (e.target.matches('#cartModal .quantity-input, .checkout_item .js_change_checkout_products')) {
      const form = e.target.closest('form[data-abcr]');
      if (form) form.requestSubmit();
    }
  });
})();
