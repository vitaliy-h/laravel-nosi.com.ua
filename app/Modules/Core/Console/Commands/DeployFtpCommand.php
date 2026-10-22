<?php

namespace App\Modules\Core\Console\Commands;

use App\Modules\Core\Services\FtpClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Throwable;

/**
 * Deploy from the developer's machine to FTP-only hosting (GitHub's runners are
 * blocked by the hosting): uploads the files changed between the commit
 * deployed last time and HEAD, then calls POST /api/deploy for migrations and
 * caches. vendor/ is not tracked, so it never goes this way — upload it by hand
 * when composer.lock changes. See docs/project/deploy.md.
 */
#[Signature('deploy:ftp
    {--full : Upload every tracked file, not only the changed ones}
    {--since= : Upload the changes since this commit instead of the one deployed last}
    {--dry-run : Only list what would be uploaded}
    {--skip-post : Do not call /api/deploy after the upload}
    {--force : Deploy even with uncommitted changes}')]
#[Description('Upload the changed files over FTP and run migrations and caches on the server')]
class DeployFtpCommand extends Command
{
    public function handle(): int
    {
        $config = config('deploy');

        foreach (['host' => $config['ftp']['host'], 'username' => $config['ftp']['username'], 'url' => $config['url'], 'token' => $config['token']] as $key => $value) {
            if (blank($value)) {
                $this->error("Deploy is not configured: set the DEPLOY_* lines in .env ({$key} is empty). See docs/project/deploy.md.");

                return self::FAILURE;
            }
        }

        if (!$this->option('force') && trim($this->git('status --porcelain --untracked-files=no')) !== '') {
            $this->error('There are uncommitted changes — commit them first (or pass --force).');

            return self::FAILURE;
        }

        $ftp = new FtpClient(
            host: $config['ftp']['host'],
            port: $config['ftp']['port'],
            username: $config['ftp']['username'],
            password: (string) $config['ftp']['password'],
            root: $config['ftp']['root'],
        );

        try {
            return $this->deploy($ftp, $config);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            $ftp->close();
        }
    }

    private function deploy(FtpClient $ftp, array $config): int
    {
        $head = trim($this->git('rev-parse HEAD'));
        $base = $this->resolveBase($ftp, $config['revision_file']);

        if ($base === $head) {
            $this->info('The server already has ' . Str::limit($head, 8, '') . ' — no code changes.');
        }

        [$files, $deleted] = $this->changedFiles($base, $head, $config['exclude']);

        $this->line($base === null
            ? 'Full upload: ' . count($files) . ' files.'
            : 'Changes ' . Str::limit($base, 8, '') . '..' . Str::limit($head, 8, '') . ': ' . count($files) . ' files to upload.');

        if (in_array('composer.lock', $files, true) && $base !== null) {
            $this->warn('composer.lock changed — upload vendor/ by hand (see docs/project/deploy.md).');
        }

        foreach ($deleted as $file) {
            $this->warn("Removed from the repository, still on the server: {$file}");
        }

        // Generated, untracked assets: compared by hash with the last upload.
        $assetHashes = $this->assetHashes($config['assets']);
        $uploadedHashes = json_decode((string) $ftp->get($config['assets_file']), true) ?: [];
        $assets = array_keys(array_filter($assetHashes, fn (string $hash, string $file) => ($uploadedHashes[$file] ?? null) !== $hash, ARRAY_FILTER_USE_BOTH));

        if ($assets !== []) {
            $this->line('Admin panel assets: ' . count($assets) . ' files to upload.');
            $files = [...$files, ...$assets];
        }

        if ($this->option('dry-run')) {
            foreach ($files as $file) {
                $this->line("  {$file}");
            }

            return self::SUCCESS;
        }

        if ($files !== []) {
            $bar = $this->output->createProgressBar(count($files));
            $bar->setFormat(' %current%/%max% [%bar%] %message%');

            foreach ($files as $file) {
                $bar->setMessage($file);
                $ftp->put(base_path($file), $file);
                $bar->advance();
            }

            $bar->setMessage('');
            $bar->finish();
            $this->newLine();
        }

        // Remembered only after every file went up: an interrupted deploy is
        // simply repeated from the same base.
        $ftp->putContents($head, $config['revision_file']);

        if ($assets !== []) {
            $ftp->putContents(json_encode($assetHashes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $config['assets_file']);
        }

        $this->info('Uploaded. Server revision: ' . Str::limit($head, 8, ''));

        return $this->option('skip-post') ? self::SUCCESS : $this->runPostDeploy($config);
    }

    private function resolveBase(FtpClient $ftp, string $revisionFile): ?string
    {
        if ($this->option('full')) {
            return null;
        }

        $base = $this->option('since') ?: trim((string) $ftp->get($revisionFile));

        if ($base === '') {
            $this->warn('The server has no deployed revision yet — uploading everything.');

            return null;
        }

        if (Process::path(base_path())->run(['git', 'cat-file', '-e', $base . '^{commit}'])->failed()) {
            throw new \RuntimeException("Commit {$base} is not in the local repository — run with --full.");
        }

        return $base;
    }

    /**
     * @param  array<int, string>  $exclude
     * @return array{0: array<int, string>, 1: array<int, string>} files to upload, files deleted since $base
     */
    private function changedFiles(?string $base, string $head, array $exclude): array
    {
        $files = $base === null
            ? $this->lines($this->git('ls-files'))
            : $this->lines($this->git("diff --name-only --diff-filter=ACMRT {$base} {$head}"));

        $deleted = $base === null ? [] : $this->lines($this->git("diff --name-only --diff-filter=D {$base} {$head}"));

        $keep = fn (string $file): bool => !Str::startsWith($file, $exclude);

        return [
            array_values(array_filter($files, fn (string $file) => $keep($file) && is_file(base_path($file)))),
            array_values(array_filter($deleted, $keep)),
        ];
    }

    /**
     * @param  array<int, string>  $folders
     * @return array<string, string> repository-relative path => md5
     */
    private function assetHashes(array $folders): array
    {
        $hashes = [];

        foreach ($folders as $folder) {
            if (!is_dir(base_path($folder))) {
                continue;
            }

            foreach (File::allFiles(base_path($folder)) as $file) {
                $path = $folder . '/' . str_replace('\\', '/', $file->getRelativePathname());
                $hashes[$path] = md5_file($file->getPathname());
            }
        }

        ksort($hashes);

        return $hashes;
    }

    private function runPostDeploy(array $config): int
    {
        $this->line('Running migrations and caches on the server…');

        $response = Http::timeout(600)
            ->acceptJson()
            ->withHeaders(['X-Deploy-Token' => $config['token']])
            ->post(rtrim($config['url'], '/') . '/api/deploy');

        foreach ($response->json('data') ?? $response->json('errors') ?? [] as $step) {
            $mark = ($step['ok'] ?? false) ? '<info>✓</info>' : '<error>✗</error>';
            $this->line("  {$mark} {$step['command']}" . (($step['ok'] ?? false) ? '' : ': ' . $step['output']));
        }

        if ($response->failed() || !$response->json('success')) {
            $this->error("POST /api/deploy answered {$response->status()}: " . ($response->json('message') ?? Str::limit($response->body(), 300)));

            return self::FAILURE;
        }

        $this->info('Deployed.');

        return self::SUCCESS;
    }

    private function git(string $arguments): string
    {
        $result = Process::path(base_path())->run('git -c core.quotepath=off ' . $arguments);

        if ($result->failed()) {
            throw new \RuntimeException('git ' . $arguments . ': ' . trim($result->errorOutput()));
        }

        return $result->output();
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $output): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $output))));
    }
}
