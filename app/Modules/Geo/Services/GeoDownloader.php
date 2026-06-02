<?php

namespace App\Modules\Geo\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GeoDownloader
{
    public function downloadAll(): void
    {
        $this->downloadPlain('countries');
        $this->downloadPlain('regions');
        $this->downloadCities();
    }

    private function downloadPlain(string $type): void
    {
        $url = config(".sources.$type");

        $path = storage_path("app/public/geo/{$type}.xml");

        $this->ensureDir($path);

        if (file_exists($path) && filesize($path) > 0) {
            return;
        }

        Http::timeout(600)
            ->sink($path)
            ->get($url)
            ->throw();
    }

    private function downloadCities(): void
    {
        $url = config('geo.sources.cities');

        $gzPath = storage_path("app/public/geo/cities.xml.gz");
        $xmlPath = storage_path("app/public/geo/cities.xml");

        $this->ensureDir($xmlPath);

        if (file_exists($xmlPath) && filesize($xmlPath) > 0) {
            return;
        }

        if (file_exists($gzPath)) {
            unlink($gzPath);
        }

        Http::timeout(600)
            ->sink($gzPath)
            ->get($url)
            ->throw();

        $this->gunzip($gzPath, $xmlPath);

        @unlink($gzPath);
    }

    private function gunzip(string $gzFile, string $outFile): void
    {
        $gz = gzopen($gzFile, 'rb');
        $out = fopen($outFile, 'wb');

        while (!gzeof($gz)) {
            fwrite($out, gzread($gz, 8192));
        }

        gzclose($gz);
        fclose($out);
    }

    private function ensureDir(string $file): void
    {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
