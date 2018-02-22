<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature;

class NomenclatureView
{
    /** @var NomenclatureItemView[] */
    public $nomenclatureItemViews;

    /**
     * @param NomenclatureItemView[] $nomenclatureItemViews
     */
    public function __construct(array $nomenclatureItemViews)
    {
        $this->nomenclatureItemViews = $nomenclatureItemViews;
    }

    public function getTree($parentReference = null, $padding = 0)
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

                $this->getTree($nomenclatureItemView->reference, $padding + 1);
            }
        }
    }
}
