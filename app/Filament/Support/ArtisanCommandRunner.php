<?php

namespace App\Filament\Support;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ArtisanCommandRunner
{
    /**
     * Whitelisted actions the Super Admin can trigger from the admin panel.
     * Keys are arbitrary action ids (shown in the UI, translated via
     * admin.debug_tools.commands.*); `command`/`params` are what actually gets
     * passed to Artisan::call(). Actions marked `destructive` require an extra
     * confirmation step in the UI.
     */
    public const COMMANDS = [
        'migrate' => [
            'params' => [],
            'destructive' => false,
        ],
        'migrate:rollback' => [
            'params' => [],
            'destructive' => true,
        ],
        'migrate:status' => [
            'params' => [],
            'destructive' => false,
        ],
        'migrate:fresh' => [
            'params' => [],
            'destructive' => true,
        ],
        'migrate:fresh --seed' => [
            'command' => 'migrate:fresh',
            'params' => ['--seed' => true],
            'destructive' => true,
        ],
        'db:seed' => [
            'params' => [],
            'destructive' => true,
        ],
        'cache:clear' => [
            'params' => [],
            'destructive' => false,
        ],
        'config:clear' => [
            'params' => [],
            'destructive' => false,
        ],
        'route:clear' => [
            'params' => [],
            'destructive' => false,
        ],
        'view:clear' => [
            'params' => [],
            'destructive' => false,
        ],
        'queue:restart' => [
            'params' => [],
            'destructive' => false,
        ],
        'optimize:clear' => [
            'params' => [],
            'destructive' => false,
        ],
    ];

    public static function isAllowed(string $key): bool
    {
        return array_key_exists($key, self::COMMANDS);
    }

    public static function isDestructive(string $key): bool
    {
        return self::COMMANDS[$key]['destructive'] ?? false;
    }

    public function run(string $key, User $actor): string
    {
        if (!self::isAllowed($key)) {
            throw new InvalidArgumentException("Action [{$key}] is not whitelisted.");
        }

        $meta = self::COMMANDS[$key];
        $command = $meta['command'] ?? $key;
        $params = $meta['params'] ?? [];

        $exitCode = Artisan::call($command, $params);
        $output = Artisan::output();

        Log::warning('[SuperAdmin] Artisan command executed from admin panel', [
            'action' => $key,
            'command' => $command,
            'params' => $params,
            'user_id' => $actor->id,
            'user_email' => $actor->email,
            'exit_code' => $exitCode,
        ]);

        return $output;
    }
}
