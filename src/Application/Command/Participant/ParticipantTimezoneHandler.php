<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantTimezoneHandler
{
    /** @var ParticipantRepositoryInterface $participantRepository */
    private $participantRepository;

    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function handle(ParticipantTimezone $command): void
    {
        $command->participant->setTimezone($command->timezone);

        $this->participantRepository->set($command->participant);
    }
}
