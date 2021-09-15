<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Index implements Command
{
    /**
     * @var SheetTemplate
     */
    public $sheetTemplate;

    /**
     * @param SheetTemplate $sheetTemplate
     */
    public function __construct(SheetTemplate $sheetTemplate)
    {
        $this->sheetTemplate = $sheetTemplate;
    }
}
