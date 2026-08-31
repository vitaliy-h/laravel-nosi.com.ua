<?php

namespace App\Modules\Core\Support;

/**
 * Minimal user-agent parser: enough to tell one browser and one platform apart
 * in the auth log. The raw user-agent string is always stored alongside, so
 * anything this misses is still recoverable.
 */
class UserAgentParser
{
    private const BROWSERS = [
        'Edge'              => '/Edg[e|A|iOS]*\//i',
        'Opera'             => '/OPR\/|Opera/i',
        'Samsung Internet'  => '/SamsungBrowser/i',
        'Chrome'            => '/Chrome|CriOS/i',
        'Firefox'           => '/Firefox|FxiOS/i',
        'Safari'            => '/Safari/i',
        'Internet Explorer' => '/MSIE|Trident/i',
    ];

    private const PLATFORMS = [
        'Android' => '/Android/i',
        'iOS'     => '/iPhone|iPad|iPod/i',
        'Windows' => '/Windows/i',
        'macOS'   => '/Macintosh|Mac OS X/i',
        'Linux'   => '/Linux/i',
    ];

    /**
     * @return array{browser: ?string, platform: ?string}
     */
    public static function parse(?string $userAgent): array
    {
        if (blank($userAgent)) {
            return ['browser' => null, 'platform' => null];
        }

        return [
            'browser'  => self::match(self::BROWSERS, $userAgent),
            'platform' => self::match(self::PLATFORMS, $userAgent),
        ];
    }

    private static function match(array $patterns, string $userAgent): ?string
    {
        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return null;
    }
}
