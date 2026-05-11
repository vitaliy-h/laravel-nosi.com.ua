<?php

use App\Modules\Core\Facades\Trans;

function t(string $key, array $replace = []): string
{
    return Trans::get($key, $replace);
}
