<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;

class ProductInfoGuesser
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var IdToProductTransformer
     */
    private $idToProductTransformer;

    /**
     * ProductInfoGuesser constructor.
     *
     * @param TemplateDataFactory    $templateDataFactory
     * @param IdToProductTransformer $idToProductTransformer
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        IdToProductTransformer $idToProductTransformer
    ) {
        $this->templateDataFactory    = $templateDataFactory;
        $this->idToProductTransformer = $idToProductTransformer;
    }

    /**
     * @param Sheet   $sheet
     * @param Product $product
     * @param string  $locale
     *
     * @return null|Product
     */
    public function guessProduct(Sheet $sheet, Product $product, $locale)
    {
        $template = $this->templateDataFactory->createFromSheet($sheet, $locale);

        foreach ($template->getObjects() as $object) {
            if (!$object->getSelectedProduct()) {
                continue;
            }

            $linkedProduct = $this->idToProductTransformer->transform($object->getSelectedProduct());

            if (null !== $linkedProduct && $product === $linkedProduct) {
                return $linkedProduct;
            }
        }

        return null;
    }
}
