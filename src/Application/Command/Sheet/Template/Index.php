<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Index
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
