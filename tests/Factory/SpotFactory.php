<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;

final class SpotFactory
{
    /**
     * @param Event|null $event
     * @param string     $ref
     *
     * @return Spot
     */
    public static function create(Event $event = null, $ref = 'ref')
    {
        if (null === $event) {
            $event = EventFactory::createEvent();
        }

        return new Spot($ref, $event, 2, 3, 4, true);
    }
}
