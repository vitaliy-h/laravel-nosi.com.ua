<div class="col-md-6 col-lg-3 {{ $colClass ?? '' }}"
    @foreach($dataAttrs ?? [] as $attr => $value) {{ $attr }}="{{ $value }}"@endforeach>
    <div class="product-card">
        <div class="product-image">
            <img src="{{ $product->imageUrl('thumb') }}" class="img-fluid default-image" alt="{{ $product->title }}" loading="lazy">
            <img src="{{ $product->imageUrl('thumb') }}" class="img-fluid hover-image" alt="{{ $product->title }}" loading="lazy">
            @php $badge = [['class' => 'bg-accent', 'label' => 'New'], ['class' => 'bg-sale', 'label' => 'Sale'], ['class' => 'bg-sold-out', 'label' => 'Sold Out']][array_rand([0,1,2])]; @endphp
            <div class="product-tags">
                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            </div>
            <div class="product-actions">
                <button class="btn-wishlist" type="button" aria-label="Add to wishlist">
                    <i class="bi bi-heart"></i>
                </button>
            </div>
        </div>
        <div class="product-info">
            <h3 class="product-title">
                <a href="{{ module_url('shop', [$product->category->slug, $product->slug_with_id]) }}">
                    {{ $product->title }}
                </a>
            </h3>
            <div class="product-price">
                @if($product->price2)
                    <span class="current-price">${{ $product->price2 }}</span>
                    <span class="original-price">${{ $product->price }}</span>
                @else
                    <span class="current-price">${{ $product->price }}</span>
                @endif
            </div>
            <button type="button" class="btn btn-add-to-cart add-to-cart-btn" data-id="{{ $product->id }}" data-count="{{ session('basket.products.' . $product->id . '.count', 1) }}">
                <i class="bi bi-bag-plus me-2"></i>Add to Cart
            </button>
        </div>
    </div>
</div>
