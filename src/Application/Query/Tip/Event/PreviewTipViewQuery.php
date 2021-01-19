<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Domain\Model\Tip\Tip;

class PreviewTipViewQuery
{
    /** @var Tip */
    public $tip;

    /** @var array */
    public $pages;

    /** @var string */
    public $locale;

    /**
     * PreviewTipViewQuery constructor.
     *
     * @param Tip    $tip
     * @param string $locale
     */
    public function __construct(Tip $tip, $locale)
    {
        $this->tip    = $tip;
        $this->pages  = $tip->getPagesTranslations();
        $this->locale = $locale;
    }
}
