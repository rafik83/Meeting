<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Domain\Model\Event;

class DashboardTransactionViewQuery
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
