<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Nomenclature;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class NomenclatureItemsGetter
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory
    ) {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    public function getNomenclatureItems(Sheet $sheet, string $locale): array
    {
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

        $nomenclatureItems   = [];
        $nomenclatureObjects = $templateData->getNomenclatureObjects();

        foreach ($nomenclatureObjects as $nomenclatureObject) {
            $items = $nomenclatureObject->getData();
            if (isset($items['items'])) {
                if (!isset($nomenclatureItems[$nomenclatureObject->getObjective()])) {
                    $nomenclatureItems[$nomenclatureObject->getObjective()] = [];
                }

                $nomenclatureItems[$nomenclatureObject->getObjective()] = array_merge(
                    $nomenclatureItems[$nomenclatureObject->getObjective()],
                    $items['items']
                );
            }
        }

        return $nomenclatureItems;
    }
}
