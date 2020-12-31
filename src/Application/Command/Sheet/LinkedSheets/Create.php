<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var SheetView[] */
    public $sheetViews;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->sheetViews = [];
    }
}
