<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateNetworkingChatViewedAtHandler
{
    /** @var ParticipantRepositoryInterface $participantRepository */
    private $participantRepository;

    /** @var DateTimeInterface */
    private $dateTime;

    public function __construct(ParticipantRepositoryInterface $participantRepository, DateTimeInterface $dateTime)
    {
        $this->participantRepository = $participantRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(UpdateNetworkingChatViewedAt $command): void
    {
        $this->participantRepository->updateAllNetworkingChatViewedAt($command->user, $command->sheet->getEvent(), $this->dateTime);
    }
}
