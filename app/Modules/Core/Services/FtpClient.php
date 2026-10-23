<?php

namespace App\Modules\Core\Services;

use FTP\Connection;
use RuntimeException;

/**
 * A minimal FTP client for `deploy:ftp`: one connection at a time (the hosting
 * allows at most 5 per IP), passive mode, folders created on demand, and a
 * reconnect + retry when the hosting drops the connection mid-upload.
 */
class FtpClient
{
    private ?Connection $connection = null;

    /** @var array<string, true> remote folders known to exist */
    private array $folders = [];

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $username,
        private readonly string $password,
        private readonly string $root,
    ) {}

    public function put(string $localPath, string $remotePath, int $attempts = 3): void
    {
        $this->withRetry($attempts, function (Connection $ftp) use ($localPath, $remotePath): bool {
            $this->ensureFolder($ftp, dirname($remotePath));

            return ftp_put($ftp, $this->path($remotePath), $localPath, FTP_BINARY);
        }, "upload {$remotePath}");
    }

    public function putContents(string $contents, string $remotePath): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'ftp');
        file_put_contents($tmp, $contents);

        try {
            $this->put($tmp, $remotePath);
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * Contents of a remote file, null when it does not exist.
     */
    public function get(string $remotePath): ?string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'ftp');

        try {
            $ok = @ftp_get($this->connection(), $tmp, $this->path($remotePath), FTP_BINARY);

            return $ok ? (string) file_get_contents($tmp) : null;
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * False when there was nothing to delete.
     */
    public function delete(string $remotePath): bool
    {
        return @ftp_delete($this->connection(), $this->path($remotePath));
    }

    public function close(): void
    {
        if ($this->connection !== null) {
            @ftp_close($this->connection);
            $this->connection = null;
        }
    }

    private function withRetry(int $attempts, callable $action, string $what): void
    {
        for ($i = 1; $i <= $attempts; $i++) {
            if (@$action($this->connection())) {
                return;
            }

            // Dropped connection or a transient error: start a fresh session.
            $this->close();
            sleep($i * 2);
        }

        throw new RuntimeException("FTP: could not {$what}");
    }

    private function connection(): Connection
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $ftp = @ftp_connect($this->host, $this->port, 20);

        if ($ftp === false) {
            throw new RuntimeException("FTP: cannot connect to {$this->host}:{$this->port}");
        }

        if (!@ftp_login($ftp, $this->username, $this->password)) {
            ftp_close($ftp);

            throw new RuntimeException('FTP: login failed');
        }

        ftp_pasv($ftp, true);

        return $this->connection = $ftp;
    }

    private function ensureFolder(Connection $ftp, string $folder): void
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');

        if ($folder === '' || $folder === '.' || isset($this->folders[$folder])) {
            return;
        }

        $this->ensureFolder($ftp, dirname($folder));

        // Fails when the folder already exists — that is fine.
        @ftp_mkdir($ftp, $this->path($folder));
        $this->folders[$folder] = true;
    }

    private function path(string $relative): string
    {
        return rtrim($this->root, '/') . '/' . ltrim(str_replace('\\', '/', $relative), '/');
    }
}
