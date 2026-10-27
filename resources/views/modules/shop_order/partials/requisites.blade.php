{{-- Payment details from the dictionary (requisites group); an empty value hides its row --}}
@php($requisites = array_filter(['recipient', 'iban', 'tax_id', 'purpose'], fn ($key) => t("requisites.{$key}") !== ''))

@if($requisites)
    <div class="requisites">
        <h3>{{ t('requisites.title') }}</h3>
        <div class="requisites-list">
            @foreach($requisites as $key)
                <div class="requisites-row">
                    <div class="requisites-text">
                        <span>{{ t("requisites.{$key}_label") }}</span>
                        <b>{{ t("requisites.{$key}") }}</b>
                    </div>
                    <button type="button" class="requisites-copy" data-copy="{{ t("requisites.{$key}") }}" title="{{ t('requisites.copy') }}" aria-label="{{ t('requisites.copy') }}">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif
