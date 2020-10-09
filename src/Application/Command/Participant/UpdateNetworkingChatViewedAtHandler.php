<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateNetworkingChatViewedAtHandler
{
    /** @var ParticipantRepositoryInterface $participantRepository */
    private $participantRepository;

    /** @var DateTimeInterface */
    private $datetime;

    public function __construct(ParticipantRepositoryInterface $participantRepository, DateTimeInterface $datetime)
    {
        $this->participantRepository = $participantRepository;
        $this->datetime = $datetime;
    }

    public function handle(UpdateNetworkingChatViewedAt $command): void
    {
        $this->participantRepository->updateAllNetworkingChatViewedAt($command->user, $command->sheet->getEvent(), $this->datetime);
    }
}
