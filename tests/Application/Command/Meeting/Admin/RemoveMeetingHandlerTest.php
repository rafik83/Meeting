<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\RemoveMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\RemoveMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingUnParticipateEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class RemoveMeetingHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $participant1, $participant2;

    public function testHandle()
    {
        $this->handleFromMeeting($this->getMeeting());
    }

    /**
     * @expectedException \Proximum\Vimeet\Application\Exception\Slot\LockedException
     */
    public function testLockedException()
    {
        $this->handleFromMeeting($this->getMeeting(true));
    }

    private function handleFromMeeting(Meeting $meeting)
    {
        $dateTime = new \DateTime();
        $admin    = new Admin('admin@vimeet.com', 'salt', 'pwd', 'fr', 'patrick', 'sebastien', 'partenaire', $dateTime);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $translator        = $this->prophesize(TranslatorInterface::class);
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $requestDispatcher = $this->prophesize(RequestRepositoryInterface::class);

        if (!$meeting->isBlockedSlot()) {
            $meetingRepository->remove($meeting)->shouldBeCalled();
            $eventDispatcher->dispatch(
                Events::MEETING_REMOVED,
                new MeetingRemovedEvent(
                    [
                        $meeting->getFromSheet(),
                        $meeting->getToSheet(),
                    ],
                    [$this->participant1->reveal(), $this->participant2->reveal()]
                )
            )->shouldBeCalled();

            $eventDispatcher->dispatch(
                Events::MEETING_UN_PARTICIPATE,
                new MeetingUnParticipateEvent($this->participant1->reveal())
            )->shouldBeCalled();

            $eventDispatcher->dispatch(
                Events::MEETING_UN_PARTICIPATE,
                new MeetingUnParticipateEvent($this->participant2->reveal())
            )->shouldBeCalled();
        }

        $handler = new RemoveMeetingHandler(
            $meetingRepository->reveal(),
            $translator->reveal(),
            $eventDispatcher->reveal(),
            $requestDispatcher->reveal()
        );

        $handler->handle(new RemoveMeeting($meeting, $admin));

        $this->assertNull($meeting->getRequest()->getUpdateOrDeleteReasonMessage());
    }

    private function getMeeting($blockedSlot = false)
    {
        $dateTime = new \DateTime();
        $user = UserFactory::create('user@vimeet.com');
        $event = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $message = $this->prophesize(Meeting\Message::class);

        $request = new Meeting\Request(
            $sheet1,
            [$this->participant1->reveal()],
            $sheet2,
            [$this->participant2->reveal()],
            $dateTime,
            $user,
            $event
        );
        $request->setUpdateOrDeleteReasonMessage($message->reveal());
        $slot = new MeetingSlot($event, $dateTime, $dateTime, $blockedSlot);
        $spot = new Spot('ref', $event, 100, 200, 150, true);

        return new Meeting(
            $request,
            $slot,
            $sheet1,
            [$this->participant1->reveal()],
            $sheet2,
            [$this->participant2->reveal()],
            $dateTime,
            $spot,
            $event,
            true,
            $blockedSlot
        );
    }
}
