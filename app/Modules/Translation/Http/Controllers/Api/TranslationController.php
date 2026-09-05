<?php

namespace App\Modules\Translation\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Queries\GetTranslationsByLocaleQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends BaseApiController
{
    public function __construct(
        private readonly GetTranslationsByLocaleQuery $getTranslationsByLocale
    ) {}

    public function index(Request $request): JsonResponse
    {
        $locale = $request->input('locale', app()->getLocale());

        $translations = $this->getTranslationsByLocale->handle(
            locale: Localization::from($locale),
        );

        return $this->success($translations);
    }

    public function group(string $group, Request $request): JsonResponse
    {
        $locale = $request->input('locale', app()->getLocale());

        $translations = $this->getTranslationsByLocale->handle(
            locale: Localization::from($locale),
            group: $group,
        );

        return $this->success($translations);
    }
}
