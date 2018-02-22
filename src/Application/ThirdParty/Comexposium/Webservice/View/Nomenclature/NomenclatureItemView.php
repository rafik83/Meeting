<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature;

class NomenclatureItemView
{
    /** @var string */
    public $reference;

    /** @var null|string */
    public $parentReference;

    /** @var NomenclatureItemTranslationView[] */
    public $nomenclatureItemTranslationViews;

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
        $this->nomenclatureItemTranslationViews = $nomenclatureItemTranslationViews;
    }
}
