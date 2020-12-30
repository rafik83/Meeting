<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Domain\Model\Event;

interface EventUrlGeneratorInterface
{
    /**
     * Return absolute URL for a given event route
     *
     * @param Event  $event
     * @param string $routeName
     * @param array  $parameters
     *
     * @return string
     */
    public function generateEventAbsoluteUrl(Event $event, $routeName, $parameters = []);

    public function generateBaseEventAbsoluteUrl(Event $event): string;
}
