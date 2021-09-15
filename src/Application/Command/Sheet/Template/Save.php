<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Save implements Command
{
    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var array
     */
    public $value;

    /**
     * Save constructor.
     *
     * @param SheetTemplate $template
     * @param array         $value
     */
    public function __construct(SheetTemplate $template, array $value)
    {
        $this->template = $template;
        $this->value    = $value;
    }
}
