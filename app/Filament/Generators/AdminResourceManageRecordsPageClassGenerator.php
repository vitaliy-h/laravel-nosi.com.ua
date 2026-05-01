<?php

namespace App\Filament\Generators;

use App\Filament\Traits\CanGenerateGetTitleMethod;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Nette\PhpGenerator\ClassType;

class AdminResourceManageRecordsPageClassGenerator extends ResourceManageRecordsPageClassGenerator
{
    use CanGenerateGetTitleMethod;

    public function getHeaderActions(): array
    {
        return [];
    }

    protected function addMethodsToClass(ClassType $class): void
    {
        $this->addGetHeaderActionsMethodToClass($class);
        $this->addGetTitleMethodToClass($class);
    }
}
