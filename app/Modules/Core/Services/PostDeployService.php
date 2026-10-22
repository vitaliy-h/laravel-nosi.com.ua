<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Support\ModuleManifest;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * What a deploy would run over SSH after the files land,
 * for hosting reachable over FTP only: `php artisan deploy:ftp` uploads the code, then
 * calls POST /api/deploy, which runs these commands.
 *
 * Order matters: the module manifest resolves page-driven route prefixes from
 * the database, so it is built after migrating, and route:cache freezes
 * whatever prefixes it finds. See docs/starter/modules.md.
 */
class PostDeployService
{
    public function __construct(
        private readonly ModuleManifest $manifest,
    ) {}

    /**
     * @var array<int, array{0: string, 1: array<string, mixed>, 2: bool}> command, arguments, may fail
     */
    private const array COMMANDS = [
        // Not optimize:clear — its cache:clear needs the `cache` table, which a
        // fresh database gets only from migrate; the cache is cleared after it.
        ['config:clear', [], false],
        ['route:clear', [], false],
        ['view:clear', [], false],
        ['event:clear', [], false],
        ['migrate', ['--force' => true], false],
        ['cache:clear', [], false],
        // Shared hosting may forbid symlink(): the failure is reported in the
        // response (public/storage then has to be set up by hand) but does not
        // stop the deploy.
        ['storage:link', [], true],
        ['modules:cache', [], false],
        ['route:cache', [], false],
        ['view:cache', [], false],
    ];

    /**
     * @return array{success: bool, steps: array<int, array{command: string, ok: bool, output: string}>}
     */
    public function handle(): array
    {
        $steps = [];

        foreach (self::COMMANDS as [$command, $arguments, $mayFail]) {
            if ($command === 'storage:link' && file_exists(public_path('storage'))) {
                continue;
            }

            try {
                if ($command === 'modules:cache') {
                    // Module commands are registered for the console only, so
                    // over HTTP this does what CacheModulesCommand does.
                    $this->manifest->clear();
                    $ok = true;
                    $output = 'Cached the manifest for ' . count($this->manifest->write()) . ' module(s).';
                } else {
                    $ok = Artisan::call($command, $arguments) === 0;
                    $output = trim(Artisan::output());
                }
            } catch (Throwable $e) {
                $ok = false;
                $output = $e->getMessage();
            }

            $steps[] = ['command' => $command, 'ok' => $ok, 'output' => $output];

            if (!$ok && !$mayFail) {
                return ['success' => false, 'steps' => $steps];
            }
        }

        return ['success' => true, 'steps' => $steps];
    }
}
