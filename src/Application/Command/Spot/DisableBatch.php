<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class DisableBatch
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $ids;

    /**
     * DeleteBatch constructor.
     *
     * @param array $ids
     * @param Event $event
     */
    public function __construct(array $ids, Event $event)
    {
        $this->ids   = $ids;
        $this->event = $event;
    }
}
