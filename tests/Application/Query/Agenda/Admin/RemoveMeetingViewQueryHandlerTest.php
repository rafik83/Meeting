<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RemoveMeetingViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RemoveMeetingViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class RemoveMeetingViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
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

        if (!$meeting->getSlot()->isLocked()) {
            $meetingRepository->remove($meeting)->shouldBeCalled();
        }

        $handler = new RemoveMeetingViewQueryHandler(
            $meetingRepository->reveal(),
            $translator->reveal()
        );

        $handler->handle(new RemoveMeetingViewQuery($meeting, $admin));
    }

    private function getMeeting($lockedSlot = false)
    {
        $dateTime = new \DateTime();
        $user     = UserFactory::create('user@vimeet.com');
        $event    = EventFactory::createEvent();
        $sheet1   = SheetFactory::create($event);
        $sheet2   = SheetFactory::create($event);

        $request = new Meeting\Request($sheet1, [], $sheet2, [], $dateTime, $user);
        $slot    = new MeetingSlot($event, $dateTime, $dateTime, $lockedSlot);
        $spot    = new Spot('ref', $event, 100, 200, 150, true);

        return new Meeting($request, $slot, $sheet1, [], $sheet2, [], $dateTime, $spot);
    }
}
