<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\TemplateObjectView;
use Proximum\Vimeet\Domain\Package\Product\IncludedProductGuesser;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class TemplateObjectViewQueryHandler
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var TemplateProductGuesser
     */
    private $templateProductGuesser;

    /**
     * @var IncludedProductGuesser
     */
    private $includedProductGuesser;

    /**
     * TemplateObjectViewQueryHandler constructor.
     *
     * @param TemplateDataFactory    $templateDataFactory
     * @param TemplateProductGuesser $templateProductGuesser
     * @param IncludedProductGuesser $includedProductGuesser
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        TemplateProductGuesser $templateProductGuesser,
        IncludedProductGuesser $includedProductGuesser
    ) {
        $this->templateDataFactory    = $templateDataFactory;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->includedProductGuesser = $includedProductGuesser;
    }

    /**
     * @param TemplateObjectViewQuery $query
     *
     * @return TemplateObjectView
     */
    public function handle(TemplateObjectViewQuery $query)
    {
        $templateData       = $this->templateDataFactory->createFromSheet($query->sheet, $query->locale);
        $object             = $templateData->getObject($query->key);

        $products           = $this->templateProductGuesser->getProducts($object, $query->sheet->getPackage());
        $includedProductIds = $this->includedProductGuesser->getIncludedProductIds($query->sheet);

        // populate object variables needed in form builder
        $object->setBuyableProducts($products);
        $object->setSheet($query->sheet);

        $label = $templateData
            ->getObject($query->key)
            ->getLabel($query->locale, $query->sheet->getEvent()->getFallback());

        return new TemplateObjectView(
            $object,
            $label,
            $includedProductIds
        );
    }
}
