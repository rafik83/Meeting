<?php

namespace Proximum\Vimeet\Application\Event\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\EventDispatcher\Event;

class RecordingEvent extends Event
{
    /** @var Happening */
    public $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }
}
