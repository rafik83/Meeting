<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Tip\ConfirmationPhoneTipChecker;
use Proximum\Vimeet\Domain\User\Agenda\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class ValidationRequiredCheckerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user  = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $type  = $this->prophesize(Type::class);

        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getType()->willReturn($type->reveal());

        $datetime = new \DateTime('01/01/1900');

        $configuration = (new Configuration('leftColor', 'rightColor', 'textColor'))->setDates(
            null,
            null,
            null,
            null,
            null,
            null,
            $datetime
        );

        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $confirmationPhoneTipChecker = $this->prophesize(ConfirmationPhoneTipChecker::class);
        $userEventPhoneChecker       = $this->prophesize(UserEventPhoneChecker::class);
        $meetingRepository           = $this->prophesize(MeetingRepositoryInterface::class);

        $validationRequiredChecker = new ValidationRequiredChecker(
            $confirmationPhoneTipChecker->reveal(),
            $userEventPhoneChecker->reveal(),
            $meetingRepository->reveal(),
            new \DateTime()
        );

//        $event = $sheet->getEvent();
//
//        if ($this->isTipConfirmationPhoneEnabled($event, $sheet->getType())
//            && $this->userHasMeeting($user, $event)
//            && $this->agendaOnlineDateHasPassed($event)
//        ) {
//            return !$this->userEventPhoneChecker->isValidated($user, $event);
//        }

        $meetingRepository->hasMeetingForUserAndEvent($user, $event)->shouldBeCalled()->willReturn(true);

        $confirmationPhoneTipChecker->isEnabled(
            $event->reveal(),
            $type->reveal()
        )->shouldBeCalled()->willReturn(true);

        $userEventPhoneChecker->isValidated(
            $user->reveal(),
            $event->reveal()
        )->shouldBeCalled()->willReturn(true);

        $validationRequired = $validationRequiredChecker->handle($sheet->reveal(), $user->reveal());

        $this->assertEquals(false, $validationRequired);
    }
}
