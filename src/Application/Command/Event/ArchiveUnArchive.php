<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class ArchiveUnArchive
{
    const ARCHIVED = 'event_archived';
    const UN_ARCHIVED = 'event_un_archived';

    /** @var bool */
    public $archive = false;

    /** @var bool */
    public $unArchive = false;

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
