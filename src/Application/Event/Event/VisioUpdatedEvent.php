<?php

namespace Proximum\Vimeet\Application\Event\Event;

use Proximum\Vimeet\Application\Command\Event\Update;
use Symfony\Component\EventDispatcher;

class VisioUpdatedEvent extends EventDispatcher\Event
{
    /** @var Update */
    private $update;

    public function __construct(Update $update)
    {
        $this->update = $update;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }
}
