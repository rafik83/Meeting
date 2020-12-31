<?php

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

interface ComexposiumJobQueueInterface
{
    /**
     * @param Event    $event
     * @param string[] $registrationReferences
     */
    public function getRegistrations(Event $event, array $registrationReferences): void;

    public function exportSpot(Event $event, Admin $admin): void;
}
