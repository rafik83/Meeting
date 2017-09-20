<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\ApprovedRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipateToRequestEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\CannotBeTransformIntoMeetingOnDdayException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToApproveMeetingRequestException;
use Proximum\Vimeet\Application\View\Meeting\MeetingDdayView;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ApproveRequestHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var RequestPermissionManager */
    private $permissionManager;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var TransformRequestIntoMeetingHandler */
    private $transformRequestIntoMeetingHandler;

    /**
     * @param RequestRepositoryInterface         $requestRepository
     * @param MessageRepositoryInterface         $messageRepository
     * @param RequestPermissionManager           $permissionManager
     * @param DelayedEventDispatcher             $eventDispatcher
     * @param ValidationRequiredChecker          $validationRequiredChecker
     * @param MeetingSlotRepositoryInterface     $slotRepository
     * @param TransformRequestIntoMeetingHandler $transformRequestIntoMeetingHandler
     * @param \DateTimeInterface                 $datetime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        RequestPermissionManager $permissionManager,
        DelayedEventDispatcher $eventDispatcher,
        ValidationRequiredChecker $validationRequiredChecker,
        MeetingSlotRepositoryInterface $slotRepository,
        TransformRequestIntoMeetingHandler $transformRequestIntoMeetingHandler,
        \DateTimeInterface $datetime
    ) {
        $this->requestRepository                  = $requestRepository;
        $this->permissionManager                  = $permissionManager;
        $this->messageRepository                  = $messageRepository;
        $this->eventDispatcher                    = $eventDispatcher;
        $this->datetime                           = $datetime;
        $this->validationRequiredChecker          = $validationRequiredChecker;
        $this->slotRepository                     = $slotRepository;
        $this->transformRequestIntoMeetingHandler = $transformRequestIntoMeetingHandler;
    }

    /**
     * @param ApproveRequest $approveRequest
     *
     * @return null|MeetingDdayView
     * @throws IsNotAllowedToApproveMeetingRequestException
     * @throws CannotBeTransformIntoMeetingOnDdayException
     */
    public function handle(ApproveRequest $approveRequest): ?MeetingDdayView
    {
        if (!$this->permissionManager->isAllowedToApprove(
            $approveRequest->request,
            $approveRequest->sheet
        )) {
            throw new IsNotAllowedToApproveMeetingRequestException();
        }

        foreach ($approveRequest->request->getToParticipants() as $oldToParticipant) {
            if (!in_array($oldToParticipant, $approveRequest->participants)) {
                $approveRequest->request->removeToParticipant($oldToParticipant);
            }
        }

        foreach ($approveRequest->participants as $participant) {
            if (!$approveRequest->request->hasToParticipant($participant)) {
                $approveRequest->request->addToParticipant($participant);

                $this->eventDispatcher->dispatch(
                    Events::REQUEST_PARTICIPATE, new ParticipateToRequestEvent($participant)
                );
            }
        }

        // Add message
        if ($approveRequest->description) {
            $this->messageRepository->add(new Message(
                $approveRequest->request,
                $approveRequest->request->getToSheet(),
                $approveRequest->description,
                $this->datetime
            ));
            $approveRequest->request->setHasMessage(true);
        }

        $this->requestRepository->set($approveRequest->request->approve($this->datetime));

        $this->eventDispatcher->dispatch(
            Events::MEETING_REQUEST_APPROVED,
            new ApprovedRequestEvent($approveRequest->request)
        );

        // transform request into meeting on dday if tip validate phone enabled
        if (false === $this->validationRequiredChecker->handle($approveRequest->sheet, $approveRequest->editor, $approveRequest->locale)) {
            return $this->transformRequestIntoMeetingOnDday($approveRequest->request);
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return null|MeetingDdayView
     * @throws CannotBeTransformIntoMeetingOnDdayException
     */
    private function transformRequestIntoMeetingOnDday(Request $request): ?MeetingDdayView
    {
        $slots = $this->slotRepository->findAvailableSlotsByParticipants($request->getEvent(), $request->getAllParticipants());

        $dateTimePlus10Minutes = (clone $this->datetime)->modify('+10 min');

        // filter only next slots and removed past ones
        $slots = array_filter($slots,
            function (MeetingSlot $slot) use ($dateTimePlus10Minutes) {
                return $slot->getBegin() >= $dateTimePlus10Minutes;
            }
        );

        $isVisio = $this->isVisioMeeting($request);

        foreach ($slots as $slot) {
            try {
                $meeting = $this->transformRequestIntoMeetingHandler->handle(
                    new TransformRequestIntoMeeting($request, $slot, $isVisio)
                );

                return new MeetingDdayView(
                    $meeting->getSlot()->getBegin(),
                    $meeting->getSpot()->getReference()
                );
            } catch (\Exception $exception) {
                throw new CannotBeTransformIntoMeetingOnDdayException();
            }
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isVisioMeeting(Request $request): bool
    {
        foreach ($request->getAllParticipants() as $participant) {
            if ($participant->isVisio() === true) {
                return true;
            }
        }

        return false;
    }
}
