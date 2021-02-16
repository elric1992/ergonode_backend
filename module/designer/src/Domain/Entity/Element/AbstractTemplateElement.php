<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Designer\Domain\Entity\Element;

use Ergonode\Designer\Domain\Entity\TemplateElementInterface;
use Ergonode\Designer\Domain\ValueObject\Position;
use Ergonode\Designer\Domain\ValueObject\Size;
use JMS\Serializer\Annotation as JMS;

abstract class AbstractTemplateElement implements TemplateElementInterface
{
    /**
     * @JMS\Type("Ergonode\Designer\Domain\ValueObject\Position")
     */
    public Position $position;

    /**
     * @JMS\Type("Ergonode\Designer\Domain\ValueObject\Size")
     */
    public Size $size;

    public function __construct(Position $position, Size $size)
    {
        $this->position = $position;
        $this->size = $size;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getSize(): Size
    {
        return $this->size;
    }
}