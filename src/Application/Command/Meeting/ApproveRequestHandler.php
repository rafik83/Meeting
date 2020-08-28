<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Components\Meeting\AllowTransformRequestIntoMeetingOnDday;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\ApprovedRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipateToRequestEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\CannotBeTransformIntoMeetingOnDdayException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToApproveMeetingRequestException;
use Proximum\Vimeet\Application\Query\Meeting\MeetingDDayViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingDDayViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\ApproveRequestResult;
use Proximum\Vimeet\Application\View\Meeting\MeetingDdayView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
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

    /** @var TransformRequestIntoMeetingHandler */
    private $transformRequestIntoMeetingHandler;

    /** @var DDayGuesser */
    private $ddayGuesser;

    /** @var MeetingDDayViewQueryHandler */
    private $meetingDDayViewQueryHandler;

    /**
     * @var AllowTransformRequestIntoMeetingOnDday
     */
    private $allowTransformRequestIntoMeetingOnDday;

    public function __construct(
        AllowTransformRequestIntoMeetingOnDday $allowTransformRequestIntoMeetingOnDday,
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        RequestPermissionManager $permissionManager,
        DelayedEventDispatcher $eventDispatcher,
        ValidationRequiredChecker $validationRequiredChecker,
        TransformRequestIntoMeetingHandler $transformRequestIntoMeetingHandler,
        DDayGuesser $ddayGuesser,
        MeetingDDayViewQueryHandler $meetingDDayViewQueryHandler,
        \DateTimeInterface $datetime
    ) {
        $this->requestRepository                  = $requestRepository;
        $this->permissionManager                  = $permissionManager;
        $this->messageRepository                  = $messageRepository;
        $this->eventDispatcher                    = $eventDispatcher;
        $this->datetime                           = $datetime;
        $this->validationRequiredChecker          = $validationRequiredChecker;
        $this->transformRequestIntoMeetingHandler = $transformRequestIntoMeetingHandler;
        $this->ddayGuesser                        = $ddayGuesser;
        $this->meetingDDayViewQueryHandler = $meetingDDayViewQueryHandler;
        $this->allowTransformRequestIntoMeetingOnDday = $allowTransformRequestIntoMeetingOnDday;
    }

    /**
     * @param ApproveRequest $approveRequest
     *
     * @throws IsNotAllowedToApproveMeetingRequestException
     *
     * @return null|ApproveRequestResult
     */
    public function handle(ApproveRequest $approveRequest): ?ApproveRequestResult
    {
        $approveRequest->request->setToPriority($approveRequest->toPriority);

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

        if ($this->ddayGuesser->isItDDayAndFeatureEnabled($approveRequest->request->getEvent())) {
            if ($this->allowTransformRequestIntoMeetingOnDday->__invoke($approveRequest->request)
                || false === $this->validationRequiredChecker->handle(
                    $approveRequest->sheet,
                    $approveRequest->editor
                )
            ) {
                $meetingDdayView = $this->transformRequestIntoMeetingOnDday(
                    $approveRequest->request,
                    $approveRequest->locale
                );

                if ($meetingDdayView instanceof MeetingDdayView) {
                    return new ApproveRequestResult($meetingDdayView, false, $approveRequest->request);
                } else {
                    return new ApproveRequestResult(null, true, $approveRequest->request);
                }
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param string  $locale
     *
     * @return null|MeetingDdayView
     */
    private function transformRequestIntoMeetingOnDday(Request $request, string $locale): ?MeetingDdayView
    {
        try {
            $meeting = $this->transformRequestIntoMeetingHandler->handle(
                new TransformRequestIntoMeeting($request)
            );

            $meetingDDayView = $this->meetingDDayViewQueryHandler->handle(
                new MeetingDDayViewQuery($meeting, $locale)
            );

            return $meetingDDayView;
        } catch (CannotBeTransformIntoMeetingOnDdayException $exception) {
            return null;
        }
    }
}
