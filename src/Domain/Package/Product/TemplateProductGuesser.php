<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;

class TemplateProductGuesser
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var IdToProductTransformer
     */
    private $productTransformer;

    /**
     * @var string
     */
    private $locale;

    /**
     * TemplateProductGuesser constructor.
     *
     * @param Merger                 $orderMerger
     * @param TemplateDataFactory    $templateDataFactory
     * @param IdToProductTransformer $productTransformer
     * @param DateTimeInterface      $dateTime
     * @param string                 $locale
     */
    public function __construct(
        Merger $orderMerger,
        TemplateDataFactory $templateDataFactory,
        IdToProductTransformer $productTransformer,
        DateTimeInterface $dateTime,
        $locale
    ) {
        $this->orderMerger         = $orderMerger;
        $this->dateTime            = $dateTime;
        $this->templateDataFactory = $templateDataFactory;
        $this->productTransformer  = $productTransformer;
        $this->locale              = $locale;
    }

    /**
     * @param TemplateObject $object
     * @param Package        $package
     *
     * @return null|array
     */
    public function getProducts(TemplateObject $object, Package $package)
    {
        $products = null;

        if (!$object->isBuyable()) {
            return null;
        }

        foreach ($object->getProducts() as $productId) {
            if ($option = $package->getOptionById((int)$productId)) {
                if ($option->isBuyable($this->dateTime) && !$option->isOutOfStock()) {
                    $products[] = $option;
                }
            }
        }

        return $products;
    }

    /**
     * @param Sheet   $sheet
     * @param Product $product
     *
     * @return null|Product
     */
    public function guessProduct(Sheet $sheet, Product $product)
    {
        $template = $this->templateDataFactory->createFromSheet($sheet, $this->locale);

        foreach ($template->getObjects() as $object) {
            if (!$object->getSelectedProduct()) {
                continue;
            }

            $linkedProduct = $this->productTransformer->transform($object->getSelectedProduct());

            if (null !== $linkedProduct && $product === $linkedProduct) {
                return $linkedProduct;
            }
        }

        return null;
    }

    /**
     * Check if product option are used in multiple object on the template
     *
     * @param Sheet   $sheet
     * @param Product $product
     *
     * @return bool
     */
    public function hasSeveralUse(Sheet $sheet, Product $product)
    {
        $objects = [];

        $template = $this->templateDataFactory->createFromSheet($sheet, $this->locale);

        foreach ($template->getObjects() as $object) {
            if ($object->getSelectedProduct()) {
                $productInObject = $this->productTransformer->transform($object->getSelectedProduct());

                if ($productInObject === $product) {
                    $objects[] = $object;
                }
            }
        }

        return count($objects) > 1;
    }

    /**
     * @param TemplateObject $templateObject
     *
     * @return bool
     */
    public function hasPayableOption(TemplateObject $templateObject)
    {
        if (null === $templateObject->getBuyableProducts() || !$templateObject->isBuyable()) {
            return false;
        }

        // first order
        if (null !== $templateObject->getSheet() && !$templateObject->getSheet()->hasOrders()) {
            return true;
        }

        $order = $this->orderMerger->merge($templateObject->getSheet()->getOrders());

        foreach ($templateObject->getBuyableProducts() as $product) {
            if ($orderRow = $order->getRowForProduct($product)) {
                return $orderRow->getQuantity() <= 0;
            }
        }

        if (null === $templateObject->getSelectedProduct()) {
            return true;
        }

        return false;
    }
}
