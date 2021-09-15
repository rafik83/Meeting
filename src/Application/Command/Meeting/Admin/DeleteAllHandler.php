<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingsDeletedAllEvent;
use Proximum\Vimeet\Application\Exception\Meeting\NotAllowedToDeleteAllMeetingsException;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class DeleteAllHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        JobQueueInterface $jobQueue,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->jobQueue = $jobQueue;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DeleteAll $deleteAll
     *
     * @throws NotAllowedToDeleteAllMeetingsException
     */
    public function handle(DeleteAll $deleteAll)
    {
        if ($this->meetingPublishedAccessChecker->allowedToAccess($deleteAll->event)) {
            throw new NotAllowedToDeleteAllMeetingsException('The meetings are published, you can not delete them');
        }

        $this->meetingRepository->deleteAll($deleteAll->event);

        $this->jobQueue->indexInCatalogSheetsByEvent($deleteAll->event);
        $this->jobQueue->aggregatePhoneValidationStatus($deleteAll->event);

        $this->eventDispatcher->dispatch(
            Events::ADMIN_MEETINGS_DELETED_ALL,
            new MeetingsDeletedAllEvent($deleteAll->event, $deleteAll->admin)
        );
    }
}
