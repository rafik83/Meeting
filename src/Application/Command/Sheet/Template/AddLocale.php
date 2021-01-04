<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class AddLocale
{
    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var string
     */
    public $locale;

    /**
     * AddLocale constructor.
     *
     * @param SheetTemplate $template
     */
    public function __construct(SheetTemplate $template)
    {
        $this->template = $template;
    }
}
