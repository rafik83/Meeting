<?php

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
        $event                            = $this->prophesize(CreateRequestEvent::class);
        $eventModel                       = $this->prophesize(Event::class);
        $request                          = $this->prophesize(Request::class);
        $participant                      = $this->prophesize(Participant::class);
        $participantWithoutPhoneValidated = $this->prophesize(Participant::class);
        $participantAlreadyNotified       = $this->prophesize(Participant::class);
        $user                             = $this->prophesize(User::class);
        $userWithoutPhoneValidated        = $this->prophesize(User::class);
        $userAlreadyNotified              = $this->prophesize(User::class);
        $sheet                            = $this->prophesize(Sheet::class);
        $userEventPhone                   = $this->prophesize(User\UserEventPhone::class);
        $sms                              = $this->prophesize(SMS::class);
        $extraData                        = $this->prophesize(ExtraData::class);
        $datetime                         = new \DateTime();

        $participant->getUser()->willReturn($user->reveal());
        $participantAlreadyNotified->getUser()->willReturn($userAlreadyNotified->reveal());
        $participantWithoutPhoneValidated->getUser()->willReturn($userWithoutPhoneValidated->reveal());

        $event->getRequest()->willReturn($request);
        $request->getEvent()->willReturn($eventModel);
        $request->getToSheet()->willReturn($sheet->reveal());
        $sheet->getParticipants()->willReturn(new ArrayCollection([
            $participant->reveal(),
            $participantWithoutPhoneValidated->reveal(),
            $participantAlreadyNotified->reveal(),
        ]));

        $userEventPhone->getPhone()->willReturn('+33600000000');
        $user->getLocale()->willReturn('fr');

        $ddayGuesser           = $this->prophesize(DDayGuesser::class);
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $smsSender             = $this->prophesize(SMSSenderInterface::class);
        $extraDataRepository   = $this->prophesize(ExtraDataRepositoryInterface::class);
        $smsFactory            = $this->prophesize(SMSFactory::class);

        $ddayGuesser->isItDDayAndFeatureEnabled($eventModel)->shouldBeCalled()->willReturn(true);

        $extraDataRepository->getExtraDataForEventNameAndUser(
            $eventModel->reveal(),
            Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
            $user->reveal()
        )->shouldBeCalled()->willReturn(null);

        $extraDataRepository->getExtraDataForEventNameAndUser(
            $eventModel->reveal(),
            Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
            $userWithoutPhoneValidated->reveal()
        )->shouldBeCalled()->willReturn(null);

        $extraDataRepository->getExtraDataForEventNameAndUser(
            $eventModel->reveal(),
            Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
            $userAlreadyNotified->reveal()
        )->shouldBeCalled()->willReturn($extraData->reveal());

        $userEventPhoneChecker->getValidatedUserEventPhone(
            $user->reveal(),
            $eventModel->reveal()
        )->shouldBeCalled()->willReturn($userEventPhone->reveal());

        $userEventPhoneChecker->getValidatedUserEventPhone(
            $userWithoutPhoneValidated->reveal(),
            $eventModel->reveal()
        )->shouldBeCalled()->willReturn(null);

        $userEventPhoneChecker->getValidatedUserEventPhone(
            $userAlreadyNotified->reveal(),
            $eventModel->reveal()
        )->shouldNotBeCalled();

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
