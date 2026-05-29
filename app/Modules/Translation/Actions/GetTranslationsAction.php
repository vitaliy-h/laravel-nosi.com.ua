<?php

namespace App\Modules\Translation\Actions;

use App\Modules\Translation\Dtos\TranslationRequestData;
use App\Modules\Translation\Repositories\TranslationRepository;

readonly class GetTranslationsAction
{
    public function __construct(
        private TranslationRepository $repository
    )
    {
    }

    public function handle(TranslationRequestData $dto): array
    {

        return $this->repository->getByLocale(
            locale: $dto->locale,
            group: $dto->group,
        );
    }
}
