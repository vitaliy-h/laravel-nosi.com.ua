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
|     data-abcr="/cart/add"
|     method="POST"
| >
|
| data-abcr=""
|     abc response endpoint URL
|
| class="validate"
|     Optional jQuery validation support.
|
|--------------------------------------------------------------------------
|
| BUTTON/LINK AJAX
|
| <button
|     data-abcr="/favorite/add"
|     data-product-id="15"
| >
|
| data-abcr=""
|     abc response endpoint URL.
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
| .abcr-wait
|     Temporary class added while
|     request is processing.
|
| .abcr-disabled
|     Added to submit buttons during request.
|
|--------------------------------------------------------------------------
|
| RESPONSE FORMAT
|
| {
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
| error
| clearForm
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
|     'actions' => [
|         [
|             'method' => 'html',
|             'selector' => '#mini-cart',
|             'content' => view(
|                 'shop.cart.mini'
|             )->render(),
|         ],
|         [
|             'method' => 'text',
|             'selector' => '#cart-count',
|             'content' => Cart::count(),
|         ],
|     ],
| ]);
|
*/


// ============================================
// Response action handlers
// ============================================

const AbcResponseHandlers = {

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

    clearForm(item) {

        document.querySelectorAll(item.selector)
            .forEach(form => {

                form.querySelectorAll(
                    `
                input:not([type=submit]):not([type=button]):not([type=hidden]):not([name=captcha]),
                textarea,
                select
                `
                ).forEach(el => {

                    // checkbox / radio
                    if (
                        el.type === 'checkbox' ||
                        el.type === 'radio'
                    ) {

                        el.checked = false;

                        return;
                    }

                    // select
                    if (el.tagName === 'SELECT') {

                        el.selectedIndex = 0;

                        return;
                    }

                    // default
                    el.value = '';

                });

            });

    },

    error(item) {
        alert(item.content);
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

                el.scrollTop = 0;

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
// Handle abc response
// ============================================

function abc_response(response) {

    if (response.actions) {

        response.actions.forEach(item => {

            const handler =
                AbcResponseHandlers[
                    item.method
                    ];

            if (!handler) {

                console.warn(
                    'Unknown action method:',
                    item.method
                );

                return;

            }

            handler(item);

        });

    }

}

// ============================================
// Universal AbcResponse request layer
// ============================================

class AbcResponse {
    // Remove validation errors from previous submit attempts.
    // Otherwise old messages remain visible after user edits the form.
    static clearValidationErrors(form) {
        form?.querySelectorAll(
            '.invalid-feedback'
        ).forEach(el => el.remove());

        form?.querySelectorAll(
            '.is-invalid'
        ).forEach(el => {
            el.classList.remove(
                'is-invalid'
            );
        });

    }

    // Laravel returns validation errors in format:
    // {
    //     email: ["The email field is required."]
    // }
    static handleValidationErrors(
        errors,
        form
    ) {
        Object.entries(errors)
            .forEach(([field, messages]) => {

                const input =
                    form?.querySelector(
                        `[name="${field}"]`
                    );

                // Ignore errors for fields that do not exist in DOM.
                if (!input) {
                    return;
                }

                input.classList.add(
                    'is-invalid'
                );

                const error =
                    document.createElement(
                        'div'
                    );

                error.className =
                    'invalid-feedback';

                // Display only the first validation message.
                error.textContent =
                    messages[0];

                input.after(error);

            });

    }
    static async request({
                             url,
                             method = 'GET',
                             body = null,
                             button = null,
                             form = null,
                         }) {

        // Prevent duplicate requests caused by double-clicks.
        if (
            button?.classList.contains(
                'abcr-wait'
            )
        ) {
            return;
        }

        button?.classList.add(
            'abcr-wait'
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

            // Laravel validation failure.
            // "message": "error text",
            // "errors": {
            //     "email": [
            //         "error text"
            //      ]
            // }
            if (response.status === 422) {

                this.clearValidationErrors(form);

                const json = await response.json();

                if (json.errors) {
                    this.handleValidationErrors(
                        json.errors,
                        form
                    );
                }
                else if (json.message) {
                    alert(json.message);
                }
                else {
                    alert('error');
                }

                return;
            }

            // HTTP error
            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );

            }

            const json =
                await response.json();

            abc_response(json);

            return json;

        }
        catch (error) {

            console.error(error);

            alert('Request failed');

        }
        finally {

            setTimeout(() => {

                button?.classList.remove(
                    'abcr-wait'
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
                'form[data-abcr]'
            );

        if (!form) {
            return;
        }

        const action =
            form.dataset.abcr;

        // No ajax action = normal form
        if (!action) {
            return;
        }

        e.preventDefault();

        let valid = true;

        //todo validation
        if (
            form.classList.contains(
                'validate'
            )
        ) {

        }

        if (!valid) {
            return;
        }

        // Clear previous validation errors before each submit.
        AbcResponse.clearValidationErrors(form);

        const formData =
            new FormData(form);

        const submitButton =
            form.querySelector(
                'button[type="submit"]'
            );

        // Disable submit button while request is in progress.
        submitButton?.classList.add(
            'abcr-disabled'
        );

        const json =
            await AbcResponse.request({
                url: action,
                method:
                    form.method || 'POST',
                body: formData,
                button: submitButton,
                form: form, // Used for Laravel validation error rendering.
            });

        setTimeout(() => {

            submitButton?.classList.remove(
                'abcr-disabled'
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
                'button[data-abcr], a[data-abcr]'
            );

        if (!el) {
            return;
        }

        e.preventDefault();

        const url =
            el.dataset.abcr;

        if (!url) {
            return;
        }

        // Convert all data attributes
        // to query params
        const data = {
            ...el.dataset
        };

        delete data.abcr;

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

        await AbcResponse.request({
            url: requestUrl.toString(),
            method:
                el.dataset.method
                || 'GET',
            button: el,
        });

    }
);
