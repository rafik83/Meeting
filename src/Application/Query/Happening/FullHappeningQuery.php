<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Event;

class FullHappeningQuery
{
    /** @var ProgramView */
    public $programView;

    /** @var Event */
    public $event;

    public function __construct(ProgramView $programView, Event $event)
    {
        $this->programView = $programView;
        $this->event       = $event;
    }
}
