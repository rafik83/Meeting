<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class MeetingManager
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ParticipantManager */
    private $participantManager;

    /** @var SlotManager */
    private $slotManager;

    /** @var SpotManager */
    private $spotManager;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param ParticipantManager         $participantManager
     * @param SlotManager                $slotManager
     * @param SpotManager                $spotManager
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        ParticipantManager $participantManager,
        SlotManager $slotManager,
        SpotManager $spotManager
    ) {
        $this->meetingRepository  = $meetingRepository;
        $this->requestRepository  = $requestRepository;
        $this->participantManager = $participantManager;
        $this->slotManager        = $slotManager;
        $this->spotManager        = $spotManager;
    }

    /**
     * @param Request     $meetingRequest
     * @param MeetingSlot $slot
     * @param Spot        $spot
     *
     * @return Meeting
     */
    public function createMeetingFromRequest(Request $meetingRequest, MeetingSlot $slot, Spot $spot)
    {
        $meeting = new Meeting(
            $meetingRequest,
            $slot,
            $meetingRequest->getFromSheet(),
            $meetingRequest->getFromParticipants()->toArray(),
            $meetingRequest->getToSheet(),
            $meetingRequest->getToParticipants()->toArray(),
            new \DateTime(),
            $spot,
            false,
            false
        );

        $this->meetingRepository->add($meeting);

        return $meeting;
    }

    /**
     * @param Event $event
     *
     * @return Request
     */
    public function createMeetinRequest(Event $event)
    {
        $fromParticipant = $this->participantManager->create($event);
        $toParticipant   = $this->participantManager->create($event);

        $meetingRequest = new Request(
            $fromParticipant->getSheet(),
            [$fromParticipant],
            $toParticipant->getSheet(),
            [$toParticipant],
            new \DateTime(),
            $fromParticipant->getUser()
        );

        $this->requestRepository->add($meetingRequest);

        return $meetingRequest;
    }

    /**
     * @param Event  $event
     * @param string $spotReference
     *
     * @return Meeting
     * @throws \Exception
     */
    public function createMeetingOnSpot(Event $event, $spotReference)
    {
        $spot = $this->spotManager->getByReference($event, $spotReference);

        if (null === $spot) {
            throw new \Exception('Spot not found');
        }

        $meetingRequest = $this->createMeetinRequest($event);

        $slots = $this->slotManager->findByEvent($event);
        $slot = reset($slots);

        if (false === $slot) {
            throw new \Exception('There are no available slot for this meeting');
        }

        return $this->createMeetingFromRequest($meetingRequest, $slot, $spot);
    }
}
