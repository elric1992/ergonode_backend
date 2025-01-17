<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Importer\Infrastructure\Action;

use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\Product\Domain\Factory\ProductFactoryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use Ergonode\Product\Domain\Query\ProductQueryInterface;
use Ergonode\Product\Domain\ValueObject\Sku;
use Ergonode\Product\Domain\Repository\ProductRepositoryInterface;
use Ergonode\Product\Domain\Entity\GroupingProduct;
use Ergonode\Product\Domain\Entity\SimpleProduct;
use Ergonode\Importer\Infrastructure\Exception\ImportRelatedProductNotFoundException;
use Ergonode\Importer\Infrastructure\Exception\ImportRelatedProductIncorrectTypeException;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Designer\Domain\Query\TemplateQueryInterface;
use Ergonode\Category\Domain\Query\CategoryQueryInterface;
use Ergonode\Importer\Infrastructure\Action\Process\Product\ImportProductAttributeBuilder;
use Ergonode\Category\Domain\ValueObject\CategoryCode;
use Ergonode\SharedKernel\Domain\Aggregate\CategoryId;

class GroupingProductImportAction
{
    private ProductQueryInterface $productQuery;

    private ProductRepositoryInterface $productRepository;

    private TemplateQueryInterface $templateQuery;

    private CategoryQueryInterface $categoryQuery;

    private ImportProductAttributeBuilder $builder;

    protected ProductFactoryInterface $productFactory;

    public function __construct(
        ProductQueryInterface $productQuery,
        ProductRepositoryInterface $productRepository,
        TemplateQueryInterface $templateQuery,
        CategoryQueryInterface $categoryQuery,
        ImportProductAttributeBuilder $builder,
        ProductFactoryInterface $productFactory
    ) {
        $this->productQuery = $productQuery;
        $this->productRepository = $productRepository;
        $this->templateQuery = $templateQuery;
        $this->categoryQuery = $categoryQuery;
        $this->builder = $builder;
        $this->productFactory = $productFactory;
    }

    /**
     * @param array $categories
     * @param array $children
     * @param array $attributes
     *
     * @throws ImportRelatedProductIncorrectTypeException
     * @throws ImportRelatedProductNotFoundException
     * @throws \Exception
     */
    public function action(
        Sku $sku,
        string $template,
        array $categories,
        array $children,
        array $attributes = []
    ): GroupingProduct {
        $templateId = $this->templateQuery->findTemplateIdByCode($template);
        if (null === $templateId) {
            throw new ImportException('Missing {template} template.', ['{template}' => $template]);
        }
        $productId = $this->productQuery->findProductIdBySku($sku);
        $categories = $this->getCategories($categories);
        $attributes = $this->builder->build($attributes);
        $children = $this->getChildren($sku, $children);

        if (!$productId) {
            $productId = ProductId::generate();
            $product = $this->productFactory->create(
                GroupingProduct::TYPE,
                $productId,
                $sku,
                $templateId,
                $categories,
                $attributes,
            );
        } else {
            $product = $this->productRepository->load($productId);
            if (!$product instanceof GroupingProduct) {
                throw new ImportException('Product {sku} is not a grouping product', ['{sku}' => $sku]);
            }
            $product->changeTemplate($templateId);
            $product->changeCategories($categories);
            $product->changeAttributes($attributes);
            $product->changeChildren($children);
        }

        $this->productRepository->save($product);

        return $product;
    }

    /**
     * @param Sku[] $children
     *
     * @return AbstractProduct[]
     *
     * @throws ImportRelatedProductIncorrectTypeException
     * @throws ImportRelatedProductNotFoundException
     */
    public function getChildren(Sku $sku, array $children): array
    {
        $result = [];
        foreach ($children as $child) {
            $productId = $this->productQuery->findProductIdBySku($child);
            if (null === $productId) {
                throw new ImportRelatedProductNotFoundException($sku, $child);
            }
            $child = $this->productRepository->load($productId);
            if (!$child instanceof SimpleProduct) {
                throw new ImportRelatedProductIncorrectTypeException($sku, $child->getType());
            }
            $result[] = $child;
        }

        return $result;
    }

    /**
     * @param CategoryCode[] $categories
     *
     * @return CategoryId[]
     */
    public function getCategories(array $categories): array
    {
        $result = [];
        foreach ($categories as $category) {
            $categoryId = $this->categoryQuery->findIdByCode($category);
            if (null === $categoryId) {
                throw new ImportException('Missing {category} category', ['{category}' => $category]);
            }
            $result[] = $categoryId;
        }

        return $result;
    }
}
