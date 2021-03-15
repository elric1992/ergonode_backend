<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Segment\Tests\Infrastructure\JMS\Serializer\Handler;

use Ergonode\Segment\Domain\ValueObject\SegmentCode;
use Ergonode\Segment\Infrastructure\JMS\Serializer\Handler\SegmentCodeHandler;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\TestCase;

class SegmentCodeHandlerTest extends TestCase
{
    private SegmentCodeHandler $handler;

    private SerializationVisitorInterface $serializerVisitor;

    private DeserializationVisitorInterface $deserializerVisitor;

    private Context $context;

    protected function setUp(): void
    {
        $this->handler = new SegmentCodeHandler();
        $this->serializerVisitor = $this->createMock(SerializationVisitorInterface::class);
        $this->deserializerVisitor = $this->createMock(DeserializationVisitorInterface::class);
        $this->context = $this->createMock(Context::class);
    }

    public function testConfiguration(): void
    {
        $configurations = SegmentCodeHandler::getSubscribingMethods();
        foreach ($configurations as $configuration) {
            $this->assertArrayHasKey('direction', $configuration);
            $this->assertArrayHasKey('type', $configuration);
            $this->assertArrayHasKey('format', $configuration);
            $this->assertArrayHasKey('method', $configuration);
        }
    }

    public function testSerialize(): void
    {
        $testValue = 'code';
        $code = new SegmentCode($testValue);
        $result = $this->handler->serialize($this->serializerVisitor, $code, [], $this->context);

        $this->assertEquals($testValue, $result);
    }

    public function testDeserialize(): void
    {
        $testValue = 'code';
        $result = $this->handler->deserialize($this->deserializerVisitor, $testValue, [], $this->context);

        $this->assertEquals($testValue, (string) $result);
    }
}
