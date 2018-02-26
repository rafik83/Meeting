<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium;

use Proximum\Vimeet\Domain\Model\Event;

interface ComexposiumJobQueueInterface
{
    /**
     * @param Event    $event
     * @param string[] $registrationReferences
     */
    public function getRegistrations(Event $event, array $registrationReferences): void;
}
