<?php

namespace App\Modules\Slider\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Slider\Models\Slider;

class SlideViewModel extends BaseViewModel
{
    public function __construct(
        public readonly ?string $kicker,
        public readonly ?string $title,
        public readonly ?string $text,
        public readonly ?string $button_text,
        public readonly ?string $url,
        public readonly string $image,
    ) {}

    public static function fromSlider(Slider $slider): self
    {
        return new self(
            kicker: $slider->kicker ?: null,
            title: $slider->title ?: null,
            text: $slider->text ?: null,
            button_text: $slider->button_text ?: null,
            url: $slider->url ?: null,
            image: $slider->image,
        );
    }
}
