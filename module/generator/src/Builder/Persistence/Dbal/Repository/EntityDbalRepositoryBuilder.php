<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Generator\Builder\Persistence\Dbal\Repository;

use Ergonode\EventSourcing\Domain\AbstractAggregateRoot;
use Ergonode\EventSourcing\Infrastructure\DomainEventStoreInterface;
use Ergonode\Generator\Builder\BuilderInterface;
use Ergonode\Generator\Builder\FileBuilder;
use Ergonode\Generator\Builder\MethodBuilder;
use Ergonode\Generator\Builder\PropertyBuilder;
use Ergonode\SharedKernel\Domain\Bus\EventBusInterface;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;

class EntityDbalRepositoryBuilder implements BuilderInterface
{
    private FileBuilder $builder;

    private MethodBuilder $methodBuilder;

    private PropertyBuilder $propertyBuilder;

    public function __construct(FileBuilder $builder, MethodBuilder $methodBuilder, PropertyBuilder $propertyBuilder)
    {
        $this->builder = $builder;
        $this->methodBuilder = $methodBuilder;
        $this->propertyBuilder = $propertyBuilder;
    }

    /**
     * @param array $properties
     */
    public function build(string $module, string $entity, array $properties = []): PhpFile
    {
        $file = $this->builder->build();
        $className = sprintf('%sRepository', $entity);
        $entityClass = sprintf('Ergonode\%s\Domain\Entity\%s', ucfirst($module), $entity);
        $entityIdClass = sprintf('Ergonode\%s\Domain\Entity\%sId', ucfirst($module), $entity);
        $repositoryInterface =
            sprintf('Ergonode\%s\Domain\Repository\%sRepositoryInterface', ucfirst($module), $entity);
        $namespace = sprintf('Ergonode\%s\Persistence\Dbal\Repository', ucfirst($module));

        $phpNamespace = $file->addNamespace($namespace);

        $phpClass = $phpNamespace->addClass($className);
        $phpClass->addImplement($repositoryInterface);
        $phpClass->addComment('Autogenerated repository class');

        $properties = [
            'store' => DomainEventStoreInterface::class,
            'dispatcher' => EventBusInterface::class,
        ];

        foreach ($properties as $name => $type) {
            $phpClass->addMember($this->propertyBuilder->build($name, $type));
        }

        $phpClass->addMember($this->buildConstructor($properties));

        $property = $this->methodBuilder->build('load', ['id' => $entityIdClass], AbstractAggregateRoot::class);

        $property->addBody('$stream = $this->store->load($id);');
        $property->addBody('if ($stream->count() > 0) {');
        $property->addBody(sprintf('    $class = new \ReflectionClass(%s::class);', ucfirst($entity)));
        $property->addBody('    $aggregate = $class->newInstanceWithoutConstructor();');
        $property->addBody('    if (!$aggregate instanceof AbstractAggregateRoot) {');
        $property
            ->addBody(
                sprintf(
                    '        throw new \LogicException(sprintf(\'Impossible to initialize "%%s"\', %s::class));',
                    ucfirst($entity)
                )
            );
        $property->addBody('    }');
        $property->addBody('    $aggregate->initialize($stream);');
        $property->addBody('');
        $property->addBody('    return $aggregate;');
        $property->addBody('}');
        $property->addBody('return null;');
        $phpClass->addMember($property);

        $property =  $this->methodBuilder->build('save', ['object' => $entityClass], 'void');
        $property->addBody('$events = $object->popEvents();');
        $property->addBody('$this->store->append($object->getId(), $events);');
        $property->addBody('foreach ($events as $envelope) {');
        $property->addBody('    $this->dispatcher->dispatch($envelope);');
        $property->addBody('}');
        $phpClass->addMember($property);

        $property = $this->methodBuilder->build('exists', ['id' => $entityIdClass], 'bool');
        $property->addBody('return $this->store->load($id)->count() > 0;');
        $phpClass->addMember($property);

        return $file;
    }

    /**
     * @param array $properties
     */
    private function buildConstructor(array $properties = []): Method
    {
        $method = $this->methodBuilder->build('__construct', $properties);
        foreach (array_keys($properties) as $name) {
            $method->addBody(sprintf('$this->%s = $%s;', $name, $name));
        }

        return $method;
    }
}
