<?php

namespace App\Modules\Promo\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Promo\Enums\PromoStyleEnum;
use App\Modules\Promo\Models\Promo;

class PromoViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $style,
        public readonly ?string $kicker,
        public readonly ?string $title,
        public readonly ?string $big_text,
        public readonly ?string $text,
        public readonly ?string $button_text,
        public readonly ?string $url,
        public readonly bool $has_image,
        public readonly string $image,
        /** Resized preview (config/image_sizes.php `large`) — what the banner shows. */
        public readonly string $large_image,
    ) {}

    public static function fromPromo(Promo $promo): self
    {
        return new self(
            style: ($promo->style ?? PromoStyleEnum::PLUM)->value,
            kicker: $promo->kicker ?: null,
            title: $promo->title ?: null,
            big_text: $promo->big_text ?: null,
            text: $promo->text ?: null,
            button_text: $promo->button_text ?: null,
            url: $promo->url ?: null,
            has_image: !empty($promo->img),
            image: $promo->image,
            large_image: $promo->imageUrl('large'),
        );
    }
}
