<?php

namespace Proximum\Vimeet\Application\Event\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\EventDispatcher\Event;

class TypesUpdated extends Event
{
    /** @var Happening */
    private $happening;

    /**
     * @param Happening $happening
     */
    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }

    /**
     * @return Happening
     */
    public function getHappening(): Happening
    {
        return $this->happening;
    }
}
