<?php

namespace Proximum\Vimeet\Application\Event\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\EventDispatcher\Event;

class DatesUpdated extends Event
{
    /** @var Happening */
    private $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }
}
