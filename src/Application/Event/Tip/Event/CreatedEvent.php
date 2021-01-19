<?php

namespace Proximum\Vimeet\Application\Event\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event as DomainEvent;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\EventDispatcher\Event;

class CreatedEvent extends Event
{
    /** @var Tip */
    private $tip;

    /**
     * @param Tip $tip
     */
    public function __construct(Tip $tip)
    {
        $this->tip = $tip;
    }

    /**
     * @return DomainEvent
     */
    public function getEvent(): DomainEvent
    {
        return $this->tip->getEvent();
    }

    /**
     * @return Tip
     */
    public function getTip(): Tip
    {
        return $this->tip;
    }
}
