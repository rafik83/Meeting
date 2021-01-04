<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\Create;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\CreateHandler;

class OnParticipantAddedHandler
{
    /** @var CreateHandler */
    private $createHandler;

    /** @var NotifyParticipantAddedHandler */
    private $notifyParticipantAddedHandler;

    public function __construct(
        CreateHandler $createHandler,
        NotifyParticipantAddedHandler $notifyParticipantAddedHandler
    ) {
        $this->createHandler = $createHandler;
        $this->notifyParticipantAddedHandler = $notifyParticipantAddedHandler;
    }

    public function handle(OnParticipantAdded $command): void
    {
        // Create the user on Comexposium
        $result = $this->createHandler->handle(
            new Create($command->event, $command->participant->getEmail(), $command->participant->getLocale())
        );

        // Send an email to the user to warn that he/she has been added to a sheet on this event
        // If the user is not created by comexposium
        // As comexposium already send an email of the other is created on there side
        if (CreateHandler::RESPONSE_CREATED !== $result) {
            $this->notifyParticipantAddedHandler->handle(
                new NotifyParticipantAdded($command->event, $command->participant, $command->participant->getLocale())
            );
        }
    }
}
