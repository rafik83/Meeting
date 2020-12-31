<?php

namespace Proximum\Vimeet\Application\Event\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Symfony\Component\EventDispatcher;

class SheetTemplateUpdatedEvent extends EventDispatcher\Event
{
    /** @var SheetTemplate */
    public $sheetTemplate;

    public function __construct(SheetTemplate $sheetTemplate)
    {
        $this->sheetTemplate = $sheetTemplate;
    }
}
