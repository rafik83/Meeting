<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\RequestIntoMeetingEvent;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransformRequestIntoMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var RequestSlotViewQueryHandler */
    private $requestSlotViewQueryHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param MeetingRepositoryInterface  $meetingRepository
     * @param SpotRepositoryInterface     $spotRepository
     * @param RequestSlotViewQueryHandler $requestSlotViewQueryHandler
     * @param \DateTimeInterface          $dateTime
     * @param EventDispatcherInterface    $eventDispatcher
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SpotRepositoryInterface $spotRepository,
        RequestSlotViewQueryHandler $requestSlotViewQueryHandler,
        \DateTimeInterface $dateTime,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->meetingRepository           = $meetingRepository;
        $this->spotRepository              = $spotRepository;
        $this->requestSlotViewQueryHandler = $requestSlotViewQueryHandler;
        $this->dateTime                    = $dateTime;
        $this->eventDispatcher             = $eventDispatcher;
    }

    /**
     * @param TransformRequestIntoMeeting $transformRequestIntoMeeting
     *
     * @throws NoSpotsAvailableForThisSlotAndMeetingException
     * @throws SlotNotAvailableForThisMeetingException
     */
    public function handle(TransformRequestIntoMeeting $transformRequestIntoMeeting)
    {
        // Get available slots
        $meetingUpdateSlotView = $this->requestSlotViewQueryHandler->handle(
            new RequestSlotViewQuery(
                $transformRequestIntoMeeting->meetingRequest,
                $transformRequestIntoMeeting->visio
            )
        );

        // Check if selected slot is in available slots
        if (false === in_array($transformRequestIntoMeeting->slot->getId(), $meetingUpdateSlotView->availableSlotsId)) {
            throw new SlotNotAvailableForThisMeetingException();
        }

        // Get available spots for this slot and meeting
        $spots = $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $transformRequestIntoMeeting->slot,
            $transformRequestIntoMeeting->meetingRequest->countParticipants(),
            null,
            $transformRequestIntoMeeting->meetingRequest->getFromSheet(),
            $transformRequestIntoMeeting->meetingRequest->getToSheet(),
            $transformRequestIntoMeeting->visio
        );

        // If no spot available
        if (0 === count($spots)) {
            throw new NoSpotsAvailableForThisSlotAndMeetingException();
        }

        // Get first spot
        $spot = reset($spots);

        $fromSheet = $transformRequestIntoMeeting->meetingRequest->getFromSheet();
        $toSheet   = $transformRequestIntoMeeting->meetingRequest->getToSheet();

        $this->meetingRepository->add(
            new Meeting(
                $transformRequestIntoMeeting->meetingRequest,
                $transformRequestIntoMeeting->slot,
                $fromSheet,
                $transformRequestIntoMeeting->meetingRequest->getParticipants($fromSheet),
                $toSheet,
                $transformRequestIntoMeeting->meetingRequest->getParticipants($toSheet),
                $this->dateTime,
                $spot
            )
        );

        $this->eventDispatcher->dispatch(
            Events::REQUEST_INTO_MEETING,
            new RequestIntoMeetingEvent([$fromSheet, $toSheet])
        );
    }
}
