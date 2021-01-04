<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteAll;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteAllHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingsDeletedAllEvent;
use Proximum\Vimeet\Application\Exception\Meeting\NotAllowedToDeleteAllMeetingsException;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DeleteAllHandlerTest extends TestCase
{
    public function testHandleWithException()
    {
        $this->expectException(NotAllowedToDeleteAllMeetingsException::class);

        // Data
        $event = EventFactory::createEvent();
        $admin = AdminFactory::create('user@vimeet.events', 'Joe', 'Cook');

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->deleteAll($event)->shouldNotBeCalled();

        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexInCatalogSheetsByEvent($event)->shouldNotBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher
            ->dispatch(
                Events::ADMIN_MEETINGS_DELETED_ALL,
                new MeetingsDeletedAllEvent($event, $admin)
            )
            ->shouldNotBeCalled();

        // Handler
        $handler = new DeleteAllHandler(
            $meetingRepository->reveal(),
            $meetingPublishedAccessChecker->reveal(),
            $jobQueue->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new DeleteAll($event, $admin));
    }

    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();
        $admin = AdminFactory::create('user@vimeet.events', 'Joe', 'Cook');

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->deleteAll($event)->shouldBeCalled();

        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexInCatalogSheetsByEvent($event)->shouldBeCalled();
        $jobQueue->aggregatePhoneValidationStatus($event)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher
            ->dispatch(
                Events::ADMIN_MEETINGS_DELETED_ALL,
                new MeetingsDeletedAllEvent($event, $admin)
            )
            ->shouldBeCalled();

        // Handler
        $handler = new DeleteAllHandler(
            $meetingRepository->reveal(),
            $meetingPublishedAccessChecker->reveal(),
            $jobQueue->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new DeleteAll($event, $admin));
    }
}
