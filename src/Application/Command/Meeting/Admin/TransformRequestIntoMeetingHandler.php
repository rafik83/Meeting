<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingRequestCanNotBeMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSpotAvailableException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQueryHandler;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class TransformRequestIntoMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestSlotViewQueryHandler */
    private $requestSlotViewQueryHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var AvailableSpots */
    private $availableSpots;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestSlotViewQueryHandler $requestSlotViewQueryHandler,
        AvailableSpots $availableSpots,
        \DateTimeInterface $dateTime,
        DelayedEventDispatcher $eventDispatcher,
        MeetingParticipants $meetingParticipants
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->requestSlotViewQueryHandler = $requestSlotViewQueryHandler;
        $this->dateTime = $dateTime;
        $this->eventDispatcher = $eventDispatcher;
        $this->availableSpots = $availableSpots;
        $this->meetingParticipants = $meetingParticipants;
    }

    /**
     * @param TransformRequestIntoMeeting $transformRequestIntoMeeting
     *
     * @throws MeetingRequestCanNotBeMeetingException
     * @throws NoSpotsAvailableForThisSlotAndMeetingException
     * @throws SlotNotAvailableForThisMeetingException
     * @throws NoSlotAvailableException
     * @throws NoSpotAvailableException
     */
    public function handle(TransformRequestIntoMeeting $transformRequestIntoMeeting): void
    {
        if (false === $transformRequestIntoMeeting->meetingRequest->isTransformableIntoMeeting()) {
            throw new MeetingRequestCanNotBeMeetingException();
        }

        // Get available slots
        $meetingUpdateSlotView = $this->requestSlotViewQueryHandler->handle(
            new RequestSlotViewQuery(
                $transformRequestIntoMeeting->meetingRequest,
                $transformRequestIntoMeeting->visio
            )
        );

        // Check if selected slot is in available slots
        if (false === \in_array(
                $transformRequestIntoMeeting->slot->getId(),
                $meetingUpdateSlotView->availableSlotsId,
                true
            )
        ) {
            throw new SlotNotAvailableForThisMeetingException();
        }

        $spot = $this->availableSpots->getBySlot(
            $transformRequestIntoMeeting->slot,
            $transformRequestIntoMeeting->meetingRequest->getFromSheet(),
            $transformRequestIntoMeeting->meetingRequest->getToSheet(),
            $this->meetingParticipants->countAllMeetingParticipants($transformRequestIntoMeeting->meetingRequest),
            $transformRequestIntoMeeting->visio
        );

        $fromSheet = $transformRequestIntoMeeting->meetingRequest->getFromSheet();
        $toSheet = $transformRequestIntoMeeting->meetingRequest->getToSheet();

        $meeting = new Meeting(
            $transformRequestIntoMeeting->meetingRequest,
            $transformRequestIntoMeeting->slot,
            $fromSheet,
            $this->meetingParticipants->getMeetingParticipants($transformRequestIntoMeeting->meetingRequest, $fromSheet),
            $toSheet,
            $this->meetingParticipants->getMeetingParticipants($transformRequestIntoMeeting->meetingRequest, $toSheet),
            $this->dateTime,
            $spot,
            $transformRequestIntoMeeting->event,
            false,
            false,
            Meeting::CREATED_BY_ADMIN
        );

        $this->meetingRepository->add($meeting);

        $this->eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent($meeting)
        );

        foreach ($meeting->getAllParticipants() as $participant) {
            $this->eventDispatcher->dispatch(
                Events::MEETING_PARTICIPATE,
                new MeetingParticipateEvent($participant)
            );
        }
    }
}
