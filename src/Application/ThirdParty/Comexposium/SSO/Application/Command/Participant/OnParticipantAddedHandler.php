<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\Create;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\CreateHandler;

class OnParticipantAddedHandler
{
    /** @var CreateHandler */
    private $createHandler;

    public function __construct(CreateHandler $createHandler)
    {
        $this->createHandler = $createHandler;
    }

    public function handle(OnParticipantAdded $command): void
    {
        // Create the user on Comexposium
        $this->createHandler->handle(
            new Create($command->event, $command->participant->getEmail(), $command->participant->getLocale())
        );

        // Send an email to the user to warn that he/she has been added to a sheet on this event
    }
}
