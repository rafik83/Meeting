<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature;

class NomenclatureItemTranslationView
{
    /** @var string */
    public $label;

    /** @var string */
    public $locale;

    public function __construct(string $label, string $locale)
    {
        $this->label = $label;
        $this->locale = $locale;
    }
}
