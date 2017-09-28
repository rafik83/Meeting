<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\MeetingRequest\SMSNotifierSubscriber;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;

class SMSNotifierSubscriberTest extends TestCase
{
    public function testOnMeetingRequestCreated()
    {
        $event          = $this->prophesize(CreateRequestEvent::class);
        $eventModel     = $this->prophesize(Event::class);
        $request        = $this->prophesize(Request::class);
        $participant    = $this->prophesize(Participant::class);
        $user           = $this->prophesize(User::class);
        $sheet          = $this->prophesize(Sheet::class);
        $userEventPhone = $this->prophesize(User\UserEventPhone::class);
        $sms            = $this->prophesize(SMS::class);
        $datetime       = new \DateTime();

        $participant->getUser()->willReturn($user->reveal());
        $event->getRequest()->willReturn($request);
        $request->getEvent()->willReturn($eventModel);
        $request->getToSheet()->willReturn($sheet->reveal());
        $sheet->getParticipants()->willReturn(new ArrayCollection([$participant->reveal()]));
        $userEventPhone->getPhone()->willReturn('+33600000000');
        $user->getLocale()->willReturn('fr');

        $ddayGuesser           = $this->prophesize(DDayGuesser::class);
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $smsSender             = $this->prophesize(SMSSenderInterface::class);
        $extraDataRepository   = $this->prophesize(ExtraDataRepositoryInterface::class);
        $smsFactory            = $this->prophesize(SMSFactory::class);

        $ddayGuesser->isItDDay($eventModel)->shouldBeCalled()->willReturn(true);

        $extraDataRepository->getExtraDataForEventNameAndUser(
            $eventModel->reveal(),
            Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
            $user->reveal()
        )->shouldBeCalled()->willReturn(null);

        $userEventPhoneChecker->getValidatedUserEventPhone(
            $user->reveal(),
            $eventModel->reveal()
        )->shouldBeCalled()->willReturn($userEventPhone->reveal());

        $smsFactory->createMeetingRequestReceive(
            '+33600000000',
            $sheet->reveal(),
            'fr'
        )->shouldBeCalled()->willReturn($sms->reveal());

        $smsSender->send($sms->reveal())->shouldBeCalled();

        $extraDataRepository->add(
            new ExtraData(
                $user->reveal(),
                $eventModel->reveal(),
                Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                $datetime->format('Y-m-d H:i:s'),
                $datetime
            )
        )->shouldBeCalled();

        $smsNotifierSubscriber = new SMSNotifierSubscriber(
            $ddayGuesser->reveal(),
            $userEventPhoneChecker->reveal(),
            $smsSender->reveal(),
            $extraDataRepository->reveal(),
            $smsFactory->reveal(),
            $datetime
        );

        $smsNotifierSubscriber->onMeetingRequestCreated($event->reveal());
    }
}
