<?php


namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class GetDisplayObjectiveFilter
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    public function __invoke(Sheet $sheet, $locale = null)
    {
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $nomenclatures = $templateData->getNomenclatureObjects();

        $objectives = [];
        foreach ($nomenclatures as $nomenclature) {

            if ($nomenclature->isNeed() && !empty($nomenclature->getItems())) {

                $objectives[] = Nomenclature::OBJECTIVE_NEED;
            }

            if ($nomenclature->isSupply() && !empty($nomenclature->getItems())) {

                $objectives[] = Nomenclature::OBJECTIVE_SUPPLY;
            }
        }

        return $objectives;
    }
}
