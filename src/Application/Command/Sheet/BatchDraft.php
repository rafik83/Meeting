<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchDraft extends AbstractBatch
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var Event
     */
    public $event;

    /**
     * BatchDraft constructor.
     *
     * @param Event $event
     * @param array $ids
     * @param Admin $admin
     */
    public function __construct(Event $event, array $ids, Admin $admin)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
        $this->event = $event;
    }
}
