<div class="site-modal site-modal--lg" id="cartModal" hidden>
    <div class="site-modal__overlay" data-modal-close></div>

    <div class="site-modal__dialog">
        <button type="button" class="site-modal__close" data-modal-close>×</button>

        <div class="site-modal__title">
            Заголовок модалки
        </div>

        <div class="site-modal__content">

            <div class="">
                <div class="">
                    <div class="cart-item aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">
                        <div class="row align-items-center gy-4">
                            <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                                <div class="product-info d-flex align-items-center">
                                    <div class="product-image">
                                        <img src="assets/img/product/product-2.webp" alt="Product" class="img-fluid" loading="lazy">
                                    </div>
                                    <div class="product-details">
                                        <h6 class="product-title">Lorem ipsum dolor sit amet</h6>
                                        <div class="product-meta">
                                            <span class="product-color">Color: Black</span>
                                            <span class="product-size">Size: M</span>
                                        </div>
                                        <button class="remove-item" type="button">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 text-center">
                                <div class="price-tag">
                                    <span class="current-price">$89.99</span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 text-center">
                                <div class="quantity-selector">
                                    <button class="quantity-btn decrease">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" class="quantity-input" value="1" min="1" max="10">
                                    <button class="quantity-btn increase">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 text-center mt-3 mt-lg-0">
                                <div class="item-total">
                                    <span>$89.99</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>




        </div>

        <div class="site-modal__actions">
            <a href="{{module_url('basket')}}" class="site-modal__button site-modal__button--primary">
                Place an order
            </a>

            <a href="{{module_url('shop')}}" class="site-modal__button site-modal__button--primary">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
