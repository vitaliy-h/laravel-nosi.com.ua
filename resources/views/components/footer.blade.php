<?php /*<footer class="bg-light mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row text-start">
            @foreach($footerMenu as $item)
                <div class="col-md-3 mb-3">
                    <ul class="list-unstyled">
                        <li class="nav-item">
                            <a class="text-decoration-none text-dark"
                               href="{{\App\Modules\Core\Facades\UrlFacade::normalize($item->slug)}}">{{$item->title}}</a>
                        </li>
                    </ul>
                </div>
            @endforeach
        </div>

        <hr>

        <div class="text-center">
            <p class="mb-0">
                © {{ date('Y') }} {!! t('footer_copyright') !!}
            </p>
        </div>
    </div>
</footer>
 */?>



<footer id="footer" class="footer">

    <div class="footer-newsletter">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2>{{ t('subscribe.title') }}</h2>
                    <p>{{ t('subscribe.text') }}</p>
                    @if(\App\Modules\Core\Support\ModuleGate::isEnabled('subscriber'))
                        {{-- No `php-email-form` class here: the demo template's validate.js would
                             hijack the submit and fire a second request alongside abc-response.js. --}}
                        <form id="newsletter-form"
                              data-abcr="{{ route('subscriber.store') }}"
                              action="{{ route('subscriber.store') }}"
                              method="POST">
                            @csrf
                            <div class="newsletter-form d-flex">
                                <input type="email" name="email" placeholder="{{ t('subscribe.placeholder') }}" required>
                                <button type="submit">{{ t('subscribe.button') }}</button>
                            </div>
                            <div id="newsletter-message" class="mt-2">
                                @if(session('success'))
                                    <div class="alert alert-success mb-0">{{ session('success') }}</div>
                                @endif
                                @error('email')
                                    <div class="alert alert-danger mb-0">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="footer-main">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="footer-widget footer-about">
                        <a href="index.html" class="logo">
                            <span class="sitename">eStore</span>
                        </a>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in nibh vehicula, facilisis magna ut, consectetur lorem.</p>
                        <div class="footer-contact mt-4">
                            <div class="contact-item">
                                <i class="bi bi-geo-alt"></i>
                                <span>123 Fashion Street, New York, NY 10001</span>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-telephone"></i>
                                <span>+1 (555) 123-4567</span>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-envelope"></i>
                                <span><a href="#">test@gmail.com</a></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-widget">
                        <h4>Shop</h4>
                        <ul class="footer-links">
                            <li><a href="category.html">New Arrivals</a></li>
                            <li><a href="category.html">Bestsellers</a></li>
                            <li><a href="category.html">Women's Clothing</a></li>
                            <li><a href="category.html">Men's Clothing</a></li>
                            <li><a href="category.html">Accessories</a></li>
                            <li><a href="category.html">Sale</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-widget">
                        <h4>Support</h4>
                        <ul class="footer-links">
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Order Status</a></li>
                            <li><a href="#">Shipping Info</a></li>
                            <li><a href="#">Returns &amp; Exchanges</a></li>
                            <li><a href="#">Size Guide</a></li>
                            <li><a href="#">Contact Us</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-widget">
                        <h4>Company</h4>
                        <ul class="footer-links">
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Press</a></li>
                            <li><a href="#">Affiliates</a></li>
                            <li><a href="#">Responsibility</a></li>
                            <li><a href="#">Investors</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="footer-widget">
                        <h4>Download Our App</h4>
                        <p>Shop on the go with our mobile app</p>
                        <div class="app-buttons">
                            <a href="#" class="app-btn">
                                <i class="bi bi-apple"></i>
                                <span>App Store</span>
                            </a>
                            <a href="#" class="app-btn">
                                <i class="bi bi-google-play"></i>
                                <span>Google Play</span>
                            </a>
                        </div>
                        <div class="social-links mt-4">
                            <h5>Follow Us</h5>
                            <div class="social-icons">
                                <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                                <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                                <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                                <a href="#" aria-label="Pinterest"><i class="bi bi-pinterest"></i></a>
                                <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">

            <div class="payment-methods d-flex align-items-center justify-content-center">
                <span>We Accept:</span>
                <div class="payment-icons">
                    <i class="bi bi-credit-card" aria-label="Credit Card"></i>
                    <i class="bi bi-paypal" aria-label="PayPal"></i>
                    <i class="bi bi-apple" aria-label="Apple Pay"></i>
                    <i class="bi bi-google" aria-label="Google Pay"></i>
                    <i class="bi bi-shop" aria-label="Shop Pay"></i>
                    <i class="bi bi-cash" aria-label="Cash on Delivery"></i>
                </div>
            </div>

            <div class="legal-links">
                @foreach($footerMenu as $item)
                    <a href="{{\App\Modules\Core\Facades\UrlFacade::normalizeForLink($item->slug)}}">{{$item->title}}</a>
                @endforeach
            </div>

            <div class="copyright text-center">
                <p>© {{ date('Y') }} {!! t('footer_copyright') !!}</p>
            </div>

        </div>

    </div>
</footer>

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Preloader -->
<div id="preloader"></div>




