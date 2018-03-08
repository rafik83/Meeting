<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
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
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class RemoveMeetingHandlerTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $participant;

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

        if (!$meeting->isBlockedSlot()) {
            $meetingRepository->remove($meeting)->shouldBeCalled();
            $eventDispatcher->dispatch(
                Events::MEETING_REMOVED,
                new MeetingRemovedEvent([
                    $meeting->getFromSheet(),
                    $meeting->getToSheet(),
                ])
            )->shouldBeCalled();

            $eventDispatcher->dispatch(
                Events::MEETING_UN_PARTICIPATE,
                new MeetingUnParticipateEvent($this->participant->reveal())
            );
        }

        $handler = new RemoveMeetingHandler(
            $meetingRepository->reveal(),
            $translator->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle(new RemoveMeeting($meeting, $admin));
    }

    private function getMeeting($blockedSlot = false)
    {
        $dateTime = new \DateTime();
        $user     = UserFactory::create('user@vimeet.com');
        $event    = EventFactory::createEvent();
        $sheet1   = SheetFactory::create($event);
        $sheet2   = SheetFactory::create($event);
        $this->participant = $this->prophesize(Participant::class);

        $request = new Meeting\Request($sheet1, [$this->participant->reveal()], $sheet2, [], $dateTime, $user, $event);
        $slot    = new MeetingSlot($event, $dateTime, $dateTime, $blockedSlot);
        $spot    = new Spot('ref', $event, 100, 200, 150, true);

        return new Meeting($request, $slot, $sheet1, [], $sheet2, [], $dateTime, $spot, $event, true, $blockedSlot);
    }
}
