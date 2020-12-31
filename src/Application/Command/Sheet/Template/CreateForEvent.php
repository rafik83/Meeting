<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Event;

class CreateForEvent
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var Event
     */
    public $event;
}
