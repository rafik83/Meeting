<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class DeleteMeetingHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event     = EventFactory::createEvent();
        $user      = UserFactory::create('toto@tata.fr');
        $user2     = UserFactory::create('titi@tutu.fr');
        $fromSheet = SheetFactory::create($event, $user);
        $toSheet   = SheetFactory::create($event, $user2);
        $slot      = SlotFactory::createSlot(1, $event);
        $spot      = SpotFactory::create($event);
        $request   = new Meeting\Request($fromSheet, [], $toSheet, [], new \DateTime(), $user, $event);
        $meeting   = new Meeting($request, $slot, $fromSheet, [], $toSheet, [], new \DateTime(), $spot, $event);
        $message = $this->prophesize(Meeting\Message::class);
        $request->setUpdateOrDeleteReasonMessage($message->reveal());

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $meetingRepository->remove($meeting)->shouldBeCalled();
        $eventDispatcher
            ->dispatch(
                Events::MEETING_REMOVED,
                new MeetingRemovedEvent(
                    [
                        $fromSheet,
                        $toSheet,
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $requestRepository->set($request)
            ->shouldBeCalled();

        // Handler
        $handler = new DeleteMeetingHandler(
            $meetingRepository->reveal(),
            $eventDispatcher->reveal(),
            $requestRepository->reveal()
        );
        $handler->handle(new DeleteMeeting($meeting));

        $this->assertNull($request->getUpdateOrDeleteReasonMessage());
    }
}
