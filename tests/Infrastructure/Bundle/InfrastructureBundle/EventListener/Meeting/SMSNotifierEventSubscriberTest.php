<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting\SMSNotifierEventSubscriber;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;

class SMSNotifierEventSubscriberTest extends TestCase
{
    public function testOnMeetingCreated()
    {
        $meeting           = $this->prophesize(Meeting::class);
        $fromSheet         = $this->prophesize(Sheet::class);
        $toSheet           = $this->prophesize(Sheet::class);
        $fromParticipant   = $this->prophesize(Participant::class);
        $toParticipant     = $this->prophesize(Participant::class);
        $userEventPhoneOne = $this->prophesize(UserEventPhone::class);
        $userEventPhoneTwo = $this->prophesize(UserEventPhone::class);
        $userOne           = $this->prophesize(User::class);
        $userTwo           = $this->prophesize(User::class);
        $event             = $this->prophesize(Event::class);
        $smsOne            = $this->prophesize(SMS::class);
        $smsTwo            = $this->prophesize(SMS::class);

        $meetingCreatedEvent = new MeetingCreatedEvent(
            $meeting->reveal()
        );

        $meeting->isCreatedByParticipants()->willReturn(true);
        $meeting->getFromParticipants()->willReturn([$fromParticipant->reveal()]);
        $meeting->getToParticipants()->willReturn([$toParticipant->reveal()]);
        $meeting->getEvent()->willReturn($event);
        $meeting->getFromSheet()->willReturn($fromSheet->reveal());
        $meeting->getToSheet()->willReturn($toSheet->reveal());
        $fromParticipant->getUser()->willReturn($userOne->reveal());
        $fromParticipant->getLocale()->willReturn('fr');
        $toParticipant->getUser()->willReturn($userTwo->reveal());
        $toParticipant->getLocale()->willReturn('fr');
        $userEventPhoneOne->getPhone()->willReturn('+3360000000');
        $userEventPhoneTwo->getPhone()->willReturn('+3361111111');

        $smsSender             = $this->prophesize(SMSSenderInterface::class);
        $smsFactory            = $this->prophesize(SMSFactory::class);
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);

        $userEventPhoneChecker->getValidatedUserEventPhone(
            $userOne->reveal(),
            $event->reveal()
        )->shouldBeCalled()->willReturn($userEventPhoneOne->reveal());

        $userEventPhoneChecker->getValidatedUserEventPhone(
            $userTwo->reveal(),
            $event->reveal()
        )->shouldBeCalled()->willReturn($userEventPhoneTwo->reveal());

        $smsFactory->createSentMeetingRequestApproved(
            '+3360000000',
            $meeting->reveal(),
            $fromSheet->reveal(),
            $toSheet->reveal(),
            $fromParticipant->reveal(),
            'fr'
        )->shouldBeCalled()->willReturn($smsOne->reveal());

        $smsFactory->createReceiveMeetingRequestApproved(
            '+3361111111',
            $meeting->reveal(),
            $toSheet->reveal(),
            $fromSheet->reveal(),
            $toParticipant->reveal(),
            'fr'
        )->shouldBeCalled()->willReturn($smsTwo->reveal());

        $smsSender->send($smsOne->reveal())->shouldBeCalled();
        $smsSender->send($smsTwo->reveal())->shouldBeCalled();

        $smsNotifierEventSubscriber = new SMSNotifierEventSubscriber(
            $smsSender->reveal(),
            $smsFactory->reveal(),
            $userEventPhoneChecker->reveal()
        );

        $smsNotifierEventSubscriber->onMeetingCreated($meetingCreatedEvent);
    }
}
