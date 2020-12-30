<?php

namespace Proximum\Vimeet\Domain\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantUnavailableAggregator
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param User  $user
     * @param Event $event
     */
    public function aggregateUnavailability(User $user, Event $event)
    {
        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);

        $firstParticipant = reset($participants);

        if (false === $firstParticipant) {
            return;
        }

        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$firstParticipant]
        );

        foreach ($participants as $participant) {
            $participant->setFullyUnavailable(empty($slots));
            $this->participantRepository->set($participant);
        }
    }
}
