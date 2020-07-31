<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\TemplateObjectView;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class TemplateObjectViewQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        TemplateProductGuesser $templateProductGuesser
    ) {
        $this->templateDataFactory    = $templateDataFactory;
        $this->templateProductGuesser = $templateProductGuesser;
    }

    public function handle(TemplateObjectViewQuery $query): TemplateObjectView
    {
        $templateData = $this->templateDataFactory->createFromSheet($query->sheet, $query->locale);
        $object = $templateData->getObject($query->key);

        $products = $this->templateProductGuesser->getProducts($object, $query->sheet->getPackage());

        // populate object variables needed in form builder
        $object->setBuyableProducts($products);
        $object->setSheet($query->sheet);

        $label = $templateData
            ->getObject($query->key)
            ->getLabel($query->locale, $query->sheet->getEvent()->getFallback());

        return new TemplateObjectView(
            $object,
            $label
        );
    }
}
