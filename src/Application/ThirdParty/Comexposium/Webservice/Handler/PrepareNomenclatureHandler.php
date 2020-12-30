<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter\RawNomenclatureToNomenclatureViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureView;

class PrepareNomenclatureHandler
{
    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var RawNomenclatureToNomenclatureViewConverter */
    private $rawNomenclatureToNomenclatureViewConverter;

    public function __construct(
        ComexposiumWebservice $comexposiumWebservice,
        RawNomenclatureToNomenclatureViewConverter $rawNomenclatureToNomenclatureViewConverter
    ) {
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->rawNomenclatureToNomenclatureViewConverter = $rawNomenclatureToNomenclatureViewConverter;
    }

    /**
     * @param string $eventReference
     *
     * @throws \SoapFault
     *
     * @return NomenclatureView
     */
    public function handle(string $eventReference): NomenclatureView
    {
        $nomenclatures = $this->comexposiumWebservice->getEventNomenclatures($eventReference);
        $nomenclatureItemViews = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureItemView = $this->rawNomenclatureToNomenclatureViewConverter->convert($nomenclature);

            if ($nomenclatureItemView instanceof NomenclatureItemView) {
                $nomenclatureItemViews[$nomenclatureItemView->reference] = $nomenclatureItemView;
            }
        }

        $this->attachParentNomenclatureItemView($nomenclatureItemViews);

        return new NomenclatureView($nomenclatureItemViews);
    }

    /**
     * @param array $nomenclatureItemViews
     */
    private function attachParentNomenclatureItemView(array &$nomenclatureItemViews): void
    {
        /** @var NomenclatureItemView $nomenclatureItemView */
        foreach ($nomenclatureItemViews as $nomenclatureItemView) {
            if (null === $nomenclatureItemView->parentReference) {
                continue;
            }

            $parentNomenclatureItemView = $nomenclatureItemViews[$nomenclatureItemView->parentReference] ?? null;

            $nomenclatureItemView->setParentNomenclatureItemView($parentNomenclatureItemView);
        }
    }
}
