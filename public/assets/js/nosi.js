/*
| Nosi storefront — behaviour of design/home.html.
| The markup is rendered by Blade; this file only wires it up.
*/
(function () {
    // Wishlist toggle (visual only for now)
    document.addEventListener('click', function (e) {
        var fav = e.target.closest('.card-fav');
        if (!fav) return;
        fav.classList.toggle('is-on');
        fav.innerHTML = fav.classList.contains('is-on') ? '<i class="bi bi-heart-fill"></i>' : '<i class="bi bi-heart"></i>';
    });

    // Add to cart: abc-response sends the form and updates .js_cart_count, here just a short confirmation
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('.card-add-form, .js-add-form');
        if (!form) return;
        var btn = form.querySelector('.card-add, [type="submit"]');
        if (!btn) return;
        btn.classList.add('is-added');
        var label = btn.dataset.added;
        var original = btn.innerHTML;
        if (label) btn.innerHTML = '<i class="bi bi-check2"></i> ' + label;
        setTimeout(function () {
            btn.classList.remove('is-added');
            if (label) btn.innerHTML = original;
        }, 1500);
    });

    // Quantity +/- (product page, cart): change the input and fire `change`
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-qty]');
        if (!btn) return;
        var input = btn.closest('.qty').querySelector('input');
        var count = (parseInt(input.value, 10) || 1) + parseInt(btn.dataset.qty, 10);
        input.value = Math.max(1, count);
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Cart page: a changed quantity is saved right away (abc-response form)
    document.addEventListener('change', function (e) {
        if (!e.target.matches('.js_change_checkout_products')) return;
        var form = e.target.closest('form[data-abcr]');
        if (form) form.requestSubmit();
    });

    // Product page: gallery thumbnails
    document.querySelectorAll('[data-gallery]').forEach(function (gallery) {
        var main = gallery.querySelector('[data-gallery-main]');
        gallery.querySelectorAll('[data-gallery-thumb]').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                main.src = thumb.dataset.galleryThumb;
                gallery.querySelectorAll('[data-gallery-thumb]').forEach(function (t) { t.classList.toggle('is-active', t === thumb); });
            });
        });
    });

    // Plain tabs: buttons [data-tab-target] switch panels [data-tab-panel] in the same [data-tabs]
    document.querySelectorAll('[data-tabs]').forEach(function (box) {
        var buttons = box.querySelectorAll('[data-tab-target]');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () { openTab(box, btn.dataset.tabTarget); });
        });
        if (location.hash && box.querySelector('[data-tab-panel="' + location.hash.slice(1) + '"]')) {
            openTab(box, location.hash.slice(1));
        }
    });
    function openTab(box, name) {
        box.querySelectorAll('[data-tab-target]').forEach(function (b) { b.classList.toggle('is-active', b.dataset.tabTarget === name); });
        box.querySelectorAll('[data-tab-panel]').forEach(function (p) { p.hidden = p.dataset.tabPanel !== name; });
    }
    // A link to a tab (#reviews) opens it
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[href^="#"]');
        if (!link) return;
        var panel = document.querySelector('[data-tab-panel="' + link.getAttribute('href').slice(1) + '"]');
        if (panel) openTab(panel.closest('[data-tabs]'), panel.dataset.tabPanel);
    });

    // Dialogs: [data-dialog-open="id"] / [data-dialog-close]
    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-dialog-open]');
        if (opener) {
            var dialog = document.getElementById(opener.dataset.dialogOpen);
            if (dialog) dialog.showModal();
            return;
        }
        var closer = e.target.closest('[data-dialog-close]');
        if (closer) closer.closest('dialog').close();
    });

    // Catalogue filters fold away on phones
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('.filter-toggle');
        if (!toggle) return;
        toggle.closest('.shop-aside').classList.toggle('is-collapsed');
    });

    // Carousels: arrows scroll one card; disabled at the ends
    document.querySelectorAll('[data-carousel]').forEach(function (box) {
        var btns = box.querySelectorAll('[data-dir]');
        if (btns.length < 2) return;
        function track() { return box.querySelector('.carousel-track:not([hidden])'); }
        function update() {
            var t = track();
            if (!t) return;
            btns[0].disabled = t.scrollLeft <= 2;
            btns[1].disabled = t.scrollLeft + t.clientWidth >= t.scrollWidth - 2;
        }
        btns.forEach(function (b) {
            b.addEventListener('click', function () {
                var t = track(), card = t && t.firstElementChild;
                if (!card) return;
                var step = card.getBoundingClientRect().width + parseFloat(getComputedStyle(t).columnGap);
                t.scrollBy({ left: step * b.dataset.dir, behavior: 'smooth' });
            });
        });
        box.querySelectorAll('.carousel-track').forEach(function (t) { t.addEventListener('scroll', update, { passive: true }); });
        window.addEventListener('resize', update);
        box.update = update;
        update();
    });

    // Tabs: top sales / new
    document.querySelectorAll('.tabs').forEach(function (tabs) {
        var section = tabs.closest('.section');
        var box = section && section.querySelector('[data-carousel]');
        if (!box) return;
        tabs.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabs.querySelectorAll('button').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                box.querySelectorAll('[data-pane]').forEach(function (p) {
                    p.hidden = p.dataset.pane !== btn.dataset.tab;
                    p.scrollLeft = 0;
                });
                if (box.update) box.update();
            });
        });
    });

    // SEO text: read more
    var seo = document.getElementById('seo-box');
    var seoBtn = document.getElementById('seo-toggle');
    if (seo && seoBtn) {
        // Short text needs no button
        if (seo.scrollHeight <= seo.clientHeight + 4) {
            seo.classList.add('is-open');
            seoBtn.hidden = true;
        }
        seoBtn.addEventListener('click', function () {
            var open = seo.classList.toggle('is-open');
            seoBtn.classList.toggle('is-open', open);
            seoBtn.firstChild.textContent = (open ? seoBtn.dataset.less : seoBtn.dataset.more) + ' ';
        });
    }

    // Mobile menu
    var nav = document.getElementById('nav');
    var toggle = document.getElementById('menu-toggle');
    if (nav && toggle) {
        var setMenu = function (open) {
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open);
            toggle.innerHTML = open ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-list"></i>';
        };
        toggle.addEventListener('click', function () { setMenu(!nav.classList.contains('is-open')); });
        nav.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    }

    // Header dropdowns: the chevron opens a category's subcategories (touch, mobile panel)
    document.querySelectorAll('.nav-sub-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.nav-item');
            var open = !item.classList.contains('is-open');
            document.querySelectorAll('.nav-item.is-open').forEach(function (other) {
                if (other !== item) {
                    other.classList.remove('is-open');
                    other.querySelector('.nav-sub-toggle').setAttribute('aria-expanded', 'false');
                }
            });
            item.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open);
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.nav-item')) {
            document.querySelectorAll('.nav-item.is-open').forEach(function (item) {
                item.classList.remove('is-open');
                item.querySelector('.nav-sub-toggle').setAttribute('aria-expanded', 'false');
            });
        }
    });

    // Order page: copy a payment detail; the icon turns into a check for a moment
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;
        var text = btn.dataset.copy;
        var done = function () {
            btn.classList.add('is-copied');
            btn.innerHTML = '<i class="bi bi-check2"></i>';
            clearTimeout(btn._copyTimer);
            btn._copyTimer = setTimeout(function () {
                btn.classList.remove('is-copied');
                btn.innerHTML = '<i class="bi bi-copy"></i>';
            }, 1500);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
            return;
        }
        // Fallback for plain http (local dev)
        var area = document.createElement('textarea');
        area.value = text;
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        try { document.execCommand('copy'); done(); } catch (err) {}
        area.remove();
    });

    // Hero slider
    var slides = document.querySelectorAll('.hero-slide');
    var dotsBox = document.getElementById('hero-dots');
    if (slides.length > 1 && dotsBox) {
        var current = 0, timer;
        slides.forEach(function (_, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', (dotsBox.dataset.label || 'Slide') + ' ' + (i + 1));
            b.addEventListener('click', function () { go(i); });
            dotsBox.appendChild(b);
        });
        var dots = dotsBox.querySelectorAll('button');
        var go = function (i) {
            slides[current].classList.remove('is-active');
            dots[current].classList.remove('is-active');
            current = (i + slides.length) % slides.length;
            slides[current].classList.add('is-active');
            dots[current].classList.add('is-active');
            clearInterval(timer);
            timer = setInterval(function () { go(current + 1); }, 6000);
        };
        document.querySelector('[data-hero="prev"]').addEventListener('click', function () { go(current - 1); });
        document.querySelector('[data-hero="next"]').addEventListener('click', function () { go(current + 1); });
        go(0);
    } else if (slides.length === 1) {
        slides[0].classList.add('is-active');
    }
})();
