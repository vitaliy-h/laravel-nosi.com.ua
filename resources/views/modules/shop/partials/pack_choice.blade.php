{{-- The sets a product is sold in («1 шт.», «5 шт.», «10 шт.»): a button per set,
     the first one picked. Picking one swaps the price ([data-pack-price], [data-pack-per])
     and the `pack` of the add-to-cart form inside the same [data-pack-scope] (nosi.js).
     Nothing to pick with a single set. --}}
@if(count($product->packs) > 1)
    <div class="pack-choice" role="radiogroup" aria-label="{{ t('shop.pack_choice') }}">
        @foreach($product->packs as $pack)
            <button type="button" role="radio" @class(['pack-btn', 'is-active' => $loop->first]) aria-checked="{{ $loop->first ? 'true' : 'false' }}"
                    data-pack="{{ $pack['quantity'] }}" data-price="{{ $pack['price_formatted'] }}" data-per="{{ $pack['per_label'] }}">{{ $pack['label'] }}</button>
        @endforeach
    </div>
@endif
