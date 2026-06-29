<?php

namespace App\Modules\Core\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('images:clear {preset? : Preset name (e.g. thumb, large)} {entity? : Entity name / table (e.g. shop_products)}')]
#[Description('Clear cached image previews')]
class ImagesClearCommand extends Command
{
    public function handle(): int
    {
        $preset = $this->argument('preset');
        $entity = $this->argument('entity');

        if ($preset === null) {
            // Clear ALL presets
            $presets = array_keys(config('image_sizes', []));

            foreach ($presets as $name) {
                $this->deleteDir($name);
            }

            $this->info('All image preview caches cleared.');
            return self::SUCCESS;
        }

        if (!array_key_exists($preset, config('image_sizes', []))) {
            $this->error("Preset [{$preset}] is not defined in config/image_sizes.php");
            return self::FAILURE;
        }

        if ($entity === null) {
            // Clear entire preset
            $this->deleteDir($preset);
            $this->info("Cleared all previews for preset [{$preset}].");
            return self::SUCCESS;
        }

        // Clear specific entity within preset
        $this->deleteDir("{$preset}/{$entity}");
        $this->info("Cleared previews for preset [{$preset}], entity [{$entity}].");
        return self::SUCCESS;
    }

    private function deleteDir(string $path): void
    {
        if (Storage::exists($path)) {
            Storage::deleteDirectory($path);
        }
    }
}
