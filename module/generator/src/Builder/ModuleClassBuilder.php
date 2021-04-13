<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Generator\Builder;

use Ergonode\SharedKernel\Application\AbstractModule;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModuleClassBuilder
{
    private FileBuilder $builder;

    public function __construct(FileBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function build(string $module): PhpFile
    {
        $file = $this->builder->build();
        $className = sprintf('Ergonode%sBundle', ucfirst($module));

        $namespace = $file->addNamespace(sprintf('Ergonode\%s', $module));
        $namespace->addUse(AbstractModule::class);
        $namespace->addUse(ContainerBuilder::class);

        $class = $namespace->addClass($className);
        $class->addComment('Autogenerated class');

        return $file;
    }
}
