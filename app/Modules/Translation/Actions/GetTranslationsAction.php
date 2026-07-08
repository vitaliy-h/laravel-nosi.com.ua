<?php

namespace App\Modules\Translation\Actions;

use App\Modules\Translation\Dtos\TranslationRequestData;
use App\Modules\Translation\Queries\GetTranslationsByLocaleQuery;

readonly class GetTranslationsAction
{
    public function __construct(
        private GetTranslationsByLocaleQuery $getTranslationsByLocale
    ) {}

    public function handle(TranslationRequestData $dto): array
    {

        return $this->getTranslationsByLocale->handle(
            locale: $dto->locale,
            group: $dto->group,
        );
    }
}
