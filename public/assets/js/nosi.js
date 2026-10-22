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
        var form = e.target.closest('.card-add-form');
        if (!form) return;
        var btn = form.querySelector('.card-add');
        if (!btn) return;
        btn.classList.add('is-added');
        setTimeout(function () { btn.classList.remove('is-added'); }, 1500);
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
        var box = tabs.closest('.section').querySelector('[data-carousel]');
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
