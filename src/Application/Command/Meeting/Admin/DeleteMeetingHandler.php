<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class DeleteMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        DelayedEventDispatcher $eventDispatcher,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param DeleteMeeting $deleteMeeting
     */
    public function handle(DeleteMeeting $deleteMeeting)
    {
        $this->meetingRepository->remove($deleteMeeting->meeting);

        $request = $deleteMeeting->meeting->getRequest();

        $request->setUpdateOrDeleteReasonMessage(null);

        $this->requestRepository->set($request);

        $this->eventDispatcher->dispatch(
            Events::MEETING_REMOVED,
            new MeetingRemovedEvent(
                [
                    $deleteMeeting->meeting->getFromSheet(),
                    $deleteMeeting->meeting->getToSheet(),
                ],
                $deleteMeeting->meeting->getAllParticipants()
            )
        );
    }
}
