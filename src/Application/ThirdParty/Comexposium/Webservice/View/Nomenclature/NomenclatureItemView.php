<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature;

class NomenclatureItemView
{
    /** @var string */
    public $reference;

    /** @var null|string */
    public $parentReference;

    /** @var NomenclatureItemTranslationView[] */
    public $nomenclatureItemTranslationViews;

    /** @var null|NomenclatureItemView */
    public $parentNomenclatureItemView;

    /**
     * @param string                            $reference
     * @param null|string                       $parentReference
     * @param NomenclatureItemTranslationView[] $nomenclatureItemTranslationViews
     */
    public function __construct(
        string $reference,
        ?string $parentReference,
        array $nomenclatureItemTranslationViews
    ) {
        $this->reference = $reference;
        $this->parentReference = $parentReference;
        $this->parentNomenclatureItemView = null;
        $this->nomenclatureItemTranslationViews = $nomenclatureItemTranslationViews;
    }

    /**
     * @param NomenclatureItemView $parentNomenclatureItemView
     */
    public function setParentNomenclatureItemView(NomenclatureItemView $parentNomenclatureItemView): void
    {
        $this->parentNomenclatureItemView = $parentNomenclatureItemView;
    }
}
