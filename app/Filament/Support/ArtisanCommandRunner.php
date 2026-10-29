<?php

namespace App\Filament\Support;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class ArtisanCommandRunner
{
    /**
     * Whitelisted actions the Super Admin can trigger from the admin panel.
     * Keys are arbitrary action ids (shown in the UI, translated via
     * admin.debug_tools.commands.*); `command`/`params` are what actually gets
     * passed to Artisan::call().
     *
     * `destructive` actions (they drop tables, roll migrations back or rewrite
     * seed data) require an extra confirmation step in the UI, and are refused
     * outright in production — see isAvailable(). Everything else is safe
     * enough to run on a live site: forward migrations and cache clearing.
     */
    public const COMMANDS = [
        'migrate' => [
            'params'      => [],
            'destructive' => false,
        ],
        'migrate:rollback' => [
            'params'      => [],
            'destructive' => true,
        ],
        'migrate:status' => [
            'params'      => [],
            'destructive' => false,
        ],
        'migrate:fresh' => [
            'params'      => [],
            'destructive' => true,
        ],
        'migrate:fresh --seed' => [
            'command'     => 'migrate:fresh',
            'params'      => ['--seed' => true],
            'destructive' => true,
        ],
        'db:seed' => [
            'params'      => [],
            'destructive' => true,
        ],
        'cache:clear' => [
            'params'      => [],
            'destructive' => false,
        ],
        'config:clear' => [
            'params'      => [],
            'destructive' => false,
        ],
        'route:clear' => [
            'params'      => [],
            'destructive' => false,
        ],
        'view:clear' => [
            'params'      => [],
            'destructive' => false,
        ],
        'queue:restart' => [
            'params'      => [],
            'destructive' => false,
        ],
        // Sends what waits in the queue (letters, SMS) and exits — for a host where
        // the cron worker is not running yet, and to see a mailer error right away.
        'queue:work --stop-when-empty' => [
            'command'     => 'queue:work',
            'params'      => ['--stop-when-empty' => true, '--tries' => 3, '--max-time' => 50],
            'destructive' => false,
        ],
        'optimize:clear' => [
            'params'      => [],
            'destructive' => false,
        ],
        // Rebuilds the list of module routes and commands — after files were
        // uploaded by hand rather than by deploy:ftp, new ones are unknown until then.
        'modules:cache' => [
            'params'      => [],
            'destructive' => false,
        ],
        // One-off on the FTP-only host: points the bot at /api/telegram/webhook on APP_URL.
        'telegram:webhook' => [
            'params'      => [],
            'destructive' => false,
        ],
        'telegram:webhook --check' => [
            'command'     => 'telegram:webhook',
            'params'      => ['--check' => true],
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

    /**
     * Whether the action can be run in the current environment.
     *
     * A destructive action wipes or rewrites data, so it stays a staging tool:
     * `migrate:fresh`, `migrate:fresh --seed`, `db:seed` and `migrate:rollback`
     * are hidden and refused once the app runs in production. The forward
     * migration and the cache/queue commands stay available everywhere.
     */
    public static function isAvailable(string $key): bool
    {
        if (!self::isAllowed($key)) {
            return false;
        }

        return !self::isDestructive($key) || !app()->isProduction();
    }

    public function run(string $key, User $actor): string
    {
        if (!self::isAllowed($key)) {
            throw new InvalidArgumentException("Action [{$key}] is not whitelisted.");
        }

        if (!self::isAvailable($key)) {
            throw new InvalidArgumentException(
                "Action [{$key}] is destructive and cannot be run in production."
            );
        }

        $meta = self::COMMANDS[$key];
        $command = $meta['command'] ?? $key;
        $params = $meta['params'] ?? [];

        // An unknown command (a stale modules cache) or a crash inside one is
        // shown as the output instead of breaking the page.
        try {
            $exitCode = Artisan::call($command, $params);
            $output = Artisan::output();
        } catch (Throwable $e) {
            $exitCode = 1;
            $output = class_basename($e) . ': ' . $e->getMessage();
        }

        Log::warning('[SuperAdmin] Artisan command executed from admin panel', [
            'action'     => $key,
            'command'    => $command,
            'params'     => $params,
            'user_id'    => $actor->id,
            'user_email' => $actor->email,
            'exit_code'  => $exitCode,
        ]);

        return $output;
    }
}
