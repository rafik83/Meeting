<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class AddParticipantToMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        ParticipantRepositoryInterface $participantRepository,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->participantRepository = $participantRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(AddParticipantToMeeting $command): void
    {
        if ($command->meeting->hasParticipant($command->participant)) {
            return;
        }

        if (!$this->participantRepository->isAvailableForMeeting([$command->participant], $command->meeting)) {
            return;
        }

        $command->meeting->addParticipant($command->participant);
        $this->meetingRepository->set($command->meeting);

        $this->eventDispatcher->dispatch(
            Events::MEETING_PARTICIPATE,
            new MeetingParticipateEvent($command->participant)
        );
    }
}
