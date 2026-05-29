<?php

namespace App\Modules\Translation\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Translation\Actions\GetTranslationsAction;
use App\Modules\Translation\Dtos\TranslationRequestData;
use App\Modules\Translation\Enums\Localization;
use Illuminate\Http\Request;

class TranslationController extends BaseApiController
{
    public function __construct(
        private readonly GetTranslationsAction $action
    )
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $locale = $request->input('locale', app()->getLocale());

        $translations = $this->action->handle(
            new TranslationRequestData(
                locale: Localization::from($locale),
            )
        );

        return $this->success($translations);
    }

    public function group(string $group, Request $request): \Illuminate\Http\JsonResponse
    {
        $locale = $request->input('locale', app()->getLocale());

        $translations = $this->action->handle(
            new TranslationRequestData(
                locale: Localization::from($locale),
                group: $group,
            )
        );

        return $this->success($translations);
    }
}
