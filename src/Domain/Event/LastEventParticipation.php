<?php

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class LastEventParticipation
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param User  $user
     * @param Event $currentEvent
     *
     * @return null|Participant
     */
    public function getLastEventParticipation(User $user, Event $currentEvent): ?Participant
    {
        if (null !== ($lastEvent = $currentEvent->getDuplicatedFrom())) {
            $participants = $this->participantRepository->getParticipantsByUserForEvent($user->getId(), $lastEvent);

            if (!empty($participants)) {
                return reset($participants);
            }
        }

        $participant = $this->participantRepository->getLastEventParticipation($user, $currentEvent);

        return $participant;
    }
}
