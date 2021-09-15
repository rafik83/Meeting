<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Update implements Command
{
    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $fallback;

    /**
     * Update constructor.
     *
     * @param SheetTemplate $template
     */
    public function __construct(SheetTemplate $template)
    {
        $this->template = $template;
        $this->title    = $template->getTitle();
        $this->fallback = $template->getFallback();
    }
}
