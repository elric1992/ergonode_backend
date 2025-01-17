<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Generator\Builder\Application\Api;

use Ergonode\Generator\Builder\FileBuilder;
use Ergonode\Generator\Builder\MethodBuilder;
use Nette\PhpGenerator\PhpFile;

class EntityControllerClassBuilder
{
    private FileBuilder $builder;

    private MethodBuilder $methodBuilder;

    public function __construct(FileBuilder $builder, MethodBuilder $methodBuilder)
    {
        $this->builder = $builder;
        $this->methodBuilder = $methodBuilder;
    }

    /**
     * @param array $properties
     */
    public function build(string $module, string $entity, array $properties = []): PhpFile
    {
        $file = $this->builder->build();
        $interfaceName = sprintf('%sFactoryInterface', $entity);

        $namespace = sprintf('Ergonode\%s\Domain\Factory', ucfirst($module));
        $entityIdClass = sprintf('Ergonode\%s\Domain\Entity\%sId', ucfirst($module), $entity);
        $entityClass = sprintf('Ergonode\%s\Domain\Entity\%s', ucfirst($module), $entity);

        $phpNamespace = $file->addNamespace($namespace);
        $phpNamespace->addUse($entityIdClass);
        $phpNamespace->addUse($entityClass);

        $phpClass = $phpNamespace->addInterface($interfaceName);
        $phpClass->addComment('Autogenerated interface');

        $method = $this->methodBuilder->build('create', ['id' => $entityIdClass], $entityClass);
        $phpClass->addMember($method);

        return $file;
    }
}
