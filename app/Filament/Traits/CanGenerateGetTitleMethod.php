<?php

namespace App\Filament\Traits;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;

trait CanGenerateGetTitleMethod
{
    protected function addGetTitleMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('getTitle')
            ->setPublic()
            ->setReturnType('string')
            ->setBody(
                <<<'PHP'
                return '';
                PHP
            );

        $this->configureGetTitleMethod($method);
    }

    protected function configureGetTitleMethod(Method $method): void {}
}
