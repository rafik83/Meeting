<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Domain\Template\TemplateData;

class SheetInfoQuery
{
    /** @var string */
    public $locale;

    /** @var TemplateData */
    public $templateData;

    /** @var string */
    public $fallback;

    /** @var array */
    public $taggedData;

    /**
     * @param TemplateData $templateData
     * @param array        $taggedData
     * @param string       $locale
     * @param string       $fallback
     */
    public function __construct(TemplateData $templateData, array $taggedData, string $locale, string $fallback)
    {
        $this->templateData = $templateData;
        $this->locale = $locale;
        $this->fallback = $fallback;
        $this->taggedData = $taggedData;
    }
}
