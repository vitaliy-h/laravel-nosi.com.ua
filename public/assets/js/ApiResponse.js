/*
|--------------------------------------------------------------------------
| AJAX Frontend Engine
|--------------------------------------------------------------------------
|
| Lightweight server-driven frontend system for Laravel ecommerce.
|
| Features:
| - AJAX forms
| - AJAX buttons/links
| - Server-side HTML rendering
| - DOM updates via response actions
| - Universal request/response handling
|
|--------------------------------------------------------------------------
| HTML API
|--------------------------------------------------------------------------
|
| FORM AJAX
|
| <form
|     class="ajax"
|     data-action="/cart/add"
|     method="POST"
| >
|
| class="ajax"
|     Enables AJAX form submit.
|
| data-action=""
|     API endpoint URL.
|
| class="validate"
|     Optional jQuery validation support.
|
|--------------------------------------------------------------------------
|
| BUTTON/LINK AJAX
|
| <button
|     data-api="/favorite/add"
|     data-product-id="15"
| >
|
| data-api=""
|     API endpoint URL.
|
| data-method=""
|     Optional HTTP method.
|     Default: GET
|
| All other data-* attributes
| are automatically converted
| to query parameters.
|
|--------------------------------------------------------------------------
|
| CSS CLASSES
|
| .js_wait
|     Temporary class added while
|     request is processing.
|
| .disabled
|     Added to submit buttons during request.
|
|--------------------------------------------------------------------------
|
| RESPONSE FORMAT
|
| {
|     "success": true,
|
|     "actions": [
|         {
|             "method": "html",
|             "selector": "#mini-cart",
|             "content": "<div>...</div>"
|         },
|
|         {
|             "method": "text",
|             "selector": "#cart-count",
|             "content": "5"
|         },
|
|         {
|             "method": "alert",
|             "content": "Added to cart"
|         }
|     ]
| }
|
|--------------------------------------------------------------------------
|
| AVAILABLE RESPONSE METHODS
|
| append
| prepend
| html
| text
| replaceWith
| scrollTop
| scroll
| show
| hide
| remove
| prop
| removeAttr
| addClass
| removeClass
| alert
| location
| reload
| modal
| script
|
|--------------------------------------------------------------------------
|
| EXAMPLE RESPONSE
|
| return response()->json([
|     'success' => true,
|
|     'actions' => [
|
|         [
|             'method' => 'html',
|             'selector' => '#mini-cart',
|             'content' => view(
|                 'shop.cart.mini'
|             )->render(),
|         ],
|
|         [
|             'method' => 'text',
|             'selector' => '#cart-count',
|             'content' => Cart::count(),
|         ],
|
|     ],
| ]);
|
*/


// ============================================
// Response action handlers
// ============================================

const ApiResponseHandlers = {

    // Insert HTML to the end of element
    append(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.insertAdjacentHTML(
                    'beforeend',
                    item.content
                );

            });

    },

    // Insert HTML to the beginning
    prepend(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.insertAdjacentHTML(
                    'afterbegin',
                    item.content
                );

            });

    },

    // Replace inner HTML
    html(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.innerHTML = item.content;

            });

    },

    // Replace text content
    text(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.textContent =
                    item.content;

            });

    },

    // Replace entire element
    replaceWith(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.replaceWith(
                    document
                        .createRange()
                        .createContextualFragment(
                            item.content
                        )
                );

            });

    },

    // Scroll container to bottom
    scrollTop(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.scrollTop =
                    el.scrollHeight;

            });

    },

    // Smooth scroll to element
    scroll(item) {

        const el =
            document.querySelector(
                item.selector
            );

        if (!el) {
            return;
        }

        el.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });

    },

    // Show elements
    show(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.style.display = '';

            });

    },

    // Hide elements
    hide(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.style.display = 'none';

            });

    },

    // Remove elements
    remove(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.remove();

            });

    },

    // Set DOM property
    prop(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el[item.prop] =
                    item.content;

            });

    },

    // Remove attribute
    removeAttr(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.removeAttribute(
                    item.content
                );

            });

    },

    // Add CSS class
    addClass(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.classList.add(
                    item.content
                );

            });

    },

    // Remove CSS class
    removeClass(item) {

        document.querySelectorAll(item.selector)
            .forEach(el => {

                el.classList.remove(
                    item.content
                );

            });

    },

    // Show alert message
    alert(item) {

        alert(item.content);

    },

    // Redirect browser
    location(item) {

        window.location.href =
            item.content;

    },

    // Reload page
    reload() {

        window.location.reload();

    },

    // Open bootstrap modal
    modal(item) {

        const modal =
            document.querySelector(
                item.selector
            );

        if (!modal) {
            return;
        }

        // Bootstrap 5 support
        if (window.bootstrap) {

            const instance =
                bootstrap.Modal
                    .getOrCreateInstance(
                        modal
                    );

            instance.show();

        }

    },

    // Execute dynamic scripts
    script(item) {

        const wrapper =
            document.createElement('div');

        wrapper.innerHTML =
            item.content;

        wrapper.querySelectorAll('script')
            .forEach(oldScript => {

                const script =
                    document.createElement(
                        'script'
                    );

                // Copy attributes
                [...oldScript.attributes]
                    .forEach(attr => {

                        script.setAttribute(
                            attr.name,
                            attr.value
                        );

                    });

                script.textContent =
                    oldScript.textContent;

                document.body
                    .appendChild(script);

                oldScript.remove();

            });

    },

};

// ============================================
// Handle API response
// ============================================

function apiResponse(response) {

    if (response.actions) {

        response.actions.forEach(item => {

            const handler =
                ApiResponseHandlers[
                    item.method
                    ];

            if (!handler) {

                console.warn(
                    'Unknown API method:',
                    item.method
                );

                return;

            }

            handler(item);

        });

    }

    // TODO:
    // Replace alerts with toast notifications
    if (response.error_text) {

        alert(response.error_text);

    }
    else if (response._error) {

        alert(response._error);

    }

}

// ============================================
// Universal API request layer
// ============================================

class Api {

    static async request({
                             url,
                             method = 'GET',
                             body = null,
                             button = null,
                         }) {

        // Prevent double click
        if (
            button?.classList.contains(
                'js_wait'
            )
        ) {
            return;
        }

        button?.classList.add(
            'js_wait'
        );

        try {

            const response =
                await fetch(url, {
                    method,
                    body,
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

                        'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            ?.content,
                    },
                });

            // HTTP error
            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );

            }

            const json =
                await response.json();

            apiResponse(json);

            return json;

        }
        catch (error) {

            console.error(error);

            alert('Request failed');

        }
        finally {

            setTimeout(() => {

                button?.classList.remove(
                    'js_wait'
                );

            }, 300);

        }

    }

}

// ============================================
// AJAX forms
// ============================================

document.addEventListener(
    'submit',
    async (e) => {

        const form =
            e.target.closest(
                'form.ajax'
            );

        if (!form) {
            return;
        }

        const action =
            form.dataset.action;

        // No ajax action = normal form
        if (!action) {
            return;
        }

        e.preventDefault();

        let valid = true;

        // Optional jQuery validation
        if (
            form.classList.contains(
                'validate'
            )
        ) {

            if (
                window.jQuery &&
                jQuery.fn.validate
            ) {

                valid =
                    jQuery(form).valid();

            }

        }

        if (!valid) {
            return;
        }

        const formData =
            new FormData(form);

        const submitButton =
            form.querySelector(
                'button[type="submit"]'
            );

        submitButton?.classList.add(
            'disabled'
        );

        const json =
            await Api.request({
                url: action,
                method:
                    form.method || 'POST',
                body: formData,
                button: submitButton,
            });

        // Clear form on success
        if (json?.success) {

            form.querySelectorAll(
                `
                input:not([type=submit])
                :not([type=hidden])
                :not([name=captcha]),
                textarea,
                select
                `
            ).forEach(el => {

                if (
                    el.type ===
                    'checkbox' ||
                    el.type === 'radio'
                ) {

                    el.checked = false;

                }
                else {

                    el.value = '';

                }

            });

        }

        setTimeout(() => {

            submitButton?.classList.remove(
                'disabled'
            );

        }, 1000);

    }
);

// ============================================
// AJAX buttons / links
// ============================================

document.addEventListener(
    'click',
    async (e) => {

        const el =
            e.target.closest(
                '[data-api]'
            );

        if (!el) {
            return;
        }

        e.preventDefault();

        const url =
            el.dataset.api;

        if (!url) {
            return;
        }

        // Convert all data attributes
        // to query params
        const data = {
            ...el.dataset
        };

        delete data.api;

        const requestUrl =
            new URL(
                url,
                window.location.origin
            );

        Object.entries(data)
            .forEach(([key, value]) => {

                requestUrl.searchParams.append(
                    key,
                    value
                );

            });

        await Api.request({
            url: requestUrl.toString(),
            method:
                el.dataset.method
                || 'GET',
            button: el,
        });

    }
);
