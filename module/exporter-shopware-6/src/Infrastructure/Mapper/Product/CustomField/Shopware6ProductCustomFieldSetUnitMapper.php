<?php
/*
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product\CustomField;

use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Attribute\Domain\Entity\Attribute\AbstractUnitAttribute;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\Product\AbstractShopware6ProductCustomFieldSetMapper;

class Shopware6ProductCustomFieldSetUnitMapper extends AbstractShopware6ProductCustomFieldSetMapper
{
    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return  AbstractUnitAttribute::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    protected function getValue(Shopware6Channel $channel, AbstractAttribute $attribute, $calculateValue): string
    {
        return (string) $calculateValue;
    }
}