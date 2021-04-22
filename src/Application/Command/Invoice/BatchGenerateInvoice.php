<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchGenerateInvoice implements Command
{
    /**
     * @var int[] of Sheet id
     */
    public $sheetIds;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event $event
     * @param array $sheetIds
     * @param Admin $admin
     */
    public function __construct(Event $event, array $sheetIds, Admin $admin)
    {
        $this->sheetIds = $sheetIds;
        $this->admin    = $admin;
        $this->event    = $event;
    }
}
