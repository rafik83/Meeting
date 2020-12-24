<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature;

class NomenclatureView
{
    /** @var NomenclatureItemView[] indexed by reference */
    public $nomenclatureItemViews;

    /**
     * @param NomenclatureItemView[] $nomenclatureItemViews indexed by reference
     */
    public function __construct(array $nomenclatureItemViews)
    {
        $this->nomenclatureItemViews = $nomenclatureItemViews;
    }

    /**
     * @param string $nomenclatureReference
     *
     * @return null|NomenclatureItemView
     */
    public function getNomenclatureItemViewByReference(string $nomenclatureReference): ?NomenclatureItemView
    {
        return $this->nomenclatureItemViews[$nomenclatureReference] ?? null;
    }

    /**
     * @param null $parentReference
     * @param int  $padding
     */
    public function showTree($parentReference = null, $padding = 0): void
    {
        if ($padding > 2) {
            return;
        }

        foreach ($this->nomenclatureItemViews as $nomenclatureItemView) {
            if ($parentReference === $nomenclatureItemView->parentReference) {
                printf(
                    "cmxp_%s%s;%s;%s\n",
                    $nomenclatureItemView->reference,
                    str_repeat(';', $padding),
                    $nomenclatureItemView->nomenclatureItemTranslationViews['fr']->label,
                    $nomenclatureItemView->nomenclatureItemTranslationViews['en']->label
                );

                $this->showTree($nomenclatureItemView->reference, $padding + 1);
            }
        }
    }
}
