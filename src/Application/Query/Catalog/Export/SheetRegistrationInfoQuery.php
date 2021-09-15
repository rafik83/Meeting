<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Domain\Template\TemplateData;

class SheetRegistrationInfoQuery
{
    /** @var TemplateData */
    public $templateData;

    /** @var string */
    public $locale;

    /** @var string */
    public $fallback;

    /**
     * @param TemplateData $templateData
     * @param string       $locale
     * @param string       $fallback
     */
    public function __construct(
        TemplateData $templateData,
        string $locale,
        string $fallback
    ) {
        $this->templateData = $templateData;
        $this->locale = $locale;
        $this->fallback = $fallback;
    }
}
