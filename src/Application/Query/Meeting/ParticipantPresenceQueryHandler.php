<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantPresenceView;
use Proximum\Vimeet\Domain\Meeting\IsParticipantPresentToMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class ParticipantPresenceQueryHandler
{
    /** @var IsParticipantPresentToMeeting */
    private $isParticipantPresentToMeeting;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        IsParticipantPresentToMeeting $isParticipantPresentToMeeting,
        MeetingRepositoryInterface $meetingRepository,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->isParticipantPresentToMeeting = $isParticipantPresentToMeeting;
        $this->meetingRepository = $meetingRepository;
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function handle(ParticipantPresenceQuery $query): array
    {
        $meetings = $this->meetingRepository->findByMeetingSlot($query->meetingSlot);
        $participantPresence = [];

        /** @var Meeting $meeting */
        foreach ($meetings as $meeting) {
            $participants = array_merge(
                $meeting->getToParticipants()->toArray(),
                $meeting->getFromParticipants()->toArray()
            );

            /** @var Participant $participant */
            foreach ($participants as $participant) {
                if (!$this->isParticipantVisio->isSatisfiedBy($participant)) {
                    continue;
                }

                $participantPresence[$participant->getId()] = new ParticipantPresenceView(
                    $participant->getId(),
                    $this->isParticipantPresentToMeeting->isSatisfiedBy($participant, $meeting)
                );
            }
        }

        return $participantPresence;
    }
}
