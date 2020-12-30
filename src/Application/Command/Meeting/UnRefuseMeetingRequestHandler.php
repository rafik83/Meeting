<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\UnRefusedRequestEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUnRefuseMeetingRequestException;
use Proximum\Vimeet\Domain\Repository\Meeting;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UnRefuseMeetingRequestHandler
{
    /**
     * @var Meeting\RequestRepositoryInterface
     */
    private $meetingRequestRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var RequestPermissionManager
     */
    private $permissionManager;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param Meeting\RequestRepositoryInterface $meetingRequestRepository
     * @param RequestPermissionManager           $permissionManager
     * @param DelayedEventDispatcher             $eventDispatcher
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        Meeting\RequestRepositoryInterface $meetingRequestRepository,
        RequestPermissionManager $permissionManager,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->permissionManager        = $permissionManager;
        $this->eventDispatcher          = $eventDispatcher;
        $this->dateTime                 = $dateTime;
    }

    /**
     * @param UnRefuseMeetingRequest $unRefuseMeetingRequest
     *
     * @throws IsNotAllowedToUnRefuseMeetingRequestException
     */
    public function handle(UnRefuseMeetingRequest $unRefuseMeetingRequest)
    {
        if (!$this->permissionManager->isAllowedToUnRefuse(
            $unRefuseMeetingRequest->meetingRequest,
            $unRefuseMeetingRequest->sheet
        )) {
            throw new IsNotAllowedToUnRefuseMeetingRequestException();
        }

        $unRefuseMeetingRequest->meetingRequest->unRefuse($this->dateTime);
        $this->meetingRequestRepository->set($unRefuseMeetingRequest->meetingRequest);

        $this->eventDispatcher->dispatch(
            Events::MEETING_REQUEST_UNREFUSED,
            new UnRefusedRequestEvent($unRefuseMeetingRequest->meetingRequest)
        );
    }
}
