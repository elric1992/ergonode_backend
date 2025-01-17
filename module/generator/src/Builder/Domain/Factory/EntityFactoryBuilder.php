<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Generator\Builder\Domain\Factory;

use Ergonode\Generator\Builder\BuilderInterface;
use Ergonode\Generator\Builder\FileBuilder;
use Ergonode\Generator\Builder\MethodBuilder;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;

class EntityFactoryBuilder implements BuilderInterface
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
        $className = sprintf('%sFactory', $entity);

        $namespace = sprintf('Ergonode\%s\Domain\Factory', ucfirst($module));
        $entityClass = sprintf('Ergonode\%s\Domain\Entity\%s', ucfirst($module), $entity);
        $entityIdClass = sprintf('Ergonode\%s\Domain\Entity\%sId', ucfirst($module), $entity);
        $factoryInterfaceClass = sprintf('Ergonode\%s\Domain\Factory\%sFactoryInterface', ucfirst($module), $entity);

        $properties = array_merge(['id' => $entityIdClass], $properties);
        $phpNamespace = $file->addNamespace($namespace);

        $phpClass = $phpNamespace->addClass($className);
        $phpClass->addImplement($factoryInterfaceClass);
        $phpClass->addComment('Autogenerated class');

        $phpClass->addMember($this->buildCreateMethod($entity, $entityClass, $properties));

        return $file;
    }

    /**
     * @param array $properties
     */
    private function buildCreateMethod(string $entity, string $class, array $properties = []): Method
    {
        $method = $this->methodBuilder->build('create', $properties, $class);
        $method->addBody(sprintf('return new %s(', ucfirst($entity)));
        foreach (array_keys($properties) as $name) {
            $keys = array_keys($properties);
            $last = end($keys);
            if ($last !== $name) {
                $method->addBody(sprintf('    $%s,', $name));
            } else {
                $method->addBody(sprintf('    $%s', $name));
            }
        }
        $method->addBody(');');

        return $method;
    }
}
