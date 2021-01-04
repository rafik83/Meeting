<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\UnapprovedRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\UnParticipateToRequestEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUnApproveMeetingRequestException;
use Proximum\Vimeet\Domain\Repository\Meeting;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UnApproveMeetingRequestHandler
{
    /**
     * @var Meeting\RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestPermissionManager
     */
    private $permissionManager;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param Meeting\RequestRepositoryInterface $requestRepository
     * @param RequestPermissionManager           $permissionManager
     * @param DelayedEventDispatcher             $eventDispatcher
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        Meeting\RequestRepositoryInterface $requestRepository,
        RequestPermissionManager $permissionManager,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->requestRepository = $requestRepository;
        $this->permissionManager = $permissionManager;
        $this->eventDispatcher   = $eventDispatcher;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param UnApproveMeetingRequest $unApproveMeetingRequest
     *
     * @throws IsNotAllowedToUnApproveMeetingRequestException
     */
    public function handle(UnApproveMeetingRequest $unApproveMeetingRequest)
    {
        if (!$this->permissionManager->isAllowedToUnApprove(
            $unApproveMeetingRequest->meetingRequest,
            $unApproveMeetingRequest->sheet
        )) {
            throw new IsNotAllowedToUnApproveMeetingRequestException();
        }

        $unApproveMeetingRequest->meetingRequest->unApprove($this->dateTime);
        $this->requestRepository->set($unApproveMeetingRequest->meetingRequest);

        $this->eventDispatcher->dispatch(
            Events::MEETING_REQUEST_UNAPPROVED,
            new UnapprovedRequestEvent($unApproveMeetingRequest->meetingRequest)
        );

        $request = $unApproveMeetingRequest->meetingRequest;

        if (!empty($request->getFromParticipantsArray()) || !empty($request->getToParticipantsArray())) {
            $participants = array_merge($request->getFromParticipantsArray(), $request->getToParticipantsArray());

            foreach ($participants as $participant) {
                $this->eventDispatcher->dispatch(
                    Events::REQUEST_UN_PARTICIPATE, new UnParticipateToRequestEvent($participant)
                );
            }
        }
    }
}
