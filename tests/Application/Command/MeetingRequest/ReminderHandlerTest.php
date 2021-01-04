<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingRequest;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Counter;
use Proximum\Vimeet\Application\Command\MeetingRequest\Remind;
use Proximum\Vimeet\Application\Command\MeetingRequest\ReminderHandler;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;

class ReminderHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $userEventPhoneRepository;

    /** @var ObjectProphecy */
    private $smsFactory;

    /** @var ObjectProphecy */
    private $smsSender;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $counter;

    /** @var ReminderHandler */
    private $handler;

    public function setUp()
    {
        $this->eventRepository          = $this->prophesize(EventRepositoryInterface::class);
        $this->extraDataRepository      = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->sheetRepository          = $this->prophesize(SheetRepositoryInterface::class);
        $this->userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $this->smsSender                = $this->prophesize(SMSSenderInterface::class);
        $this->smsFactory               = $this->prophesize(SMSFactory::class);
        $this->counter                  = $this->prophesize(Counter::class);
        $this->dateTime                 = new \DateTime();

        $this->handler = new ReminderHandler(
            $this->eventRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->userEventPhoneRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->dateTime,
            $this->smsSender->reveal(),
            $this->smsFactory->reveal(),
            $this->counter->reveal()
        );
    }

    public function testUsersWillBeNotified()
    {
        $eventOne = $this->prophesize(Event::class);
        $eventTwo = $this->prophesize(Event::class);

        $userOne = $this->prophesize(User::class);
        $userTwo = $this->prophesize(User::class);

        $sheetOne = $this->prophesize(Sheet::class);
        $sheetTwo = $this->prophesize(Sheet::class);

        $participantOne = $this->prophesize(Participant::class);
        $participantTwo = $this->prophesize(Participant::class);

        $userEventPhoneOne = $this->prophesize(User\UserEventPhone::class);
        $userEventPhoneTwo = $this->prophesize(User\UserEventPhone::class);

        $userEventPhoneOne->getUser()->willReturn($userOne->reveal());
        $userEventPhoneOne->getPhone()->willReturn('+3360000000');
        $userEventPhoneTwo->getUser()->willReturn($userTwo->reveal());

        $sheetOne->hasUserParticipant($userOne->reveal())->willReturn(true);
        $sheetOne->getUserParticipant($userOne->reveal())->willReturn($participantOne->reveal());
        $sheetTwo->hasUserParticipant($userTwo->reveal())->willReturn(true);
        $sheetTwo->getUserParticipant($userTwo->reveal())->willReturn($participantTwo->reveal());

        $userOne->getLocale()->willReturn('fr');
        $userOne->getId()->willReturn(1);
        $userTwo->getLocale()->willReturn('fr');
        $userTwo->getId()->willReturn(2);

        $eventOne->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $extraDataUserOne = $this->prophesize(User\Event\ExtraData::class);
        $extraDataUserTwo = $this->prophesize(User\Event\ExtraData::class);

        $extraDataUserOne->getUser()->willReturn($userOne->reveal());
        $extraDataUserTwo->getUser()->willReturn($userTwo->reveal());

        $smsOne = $this->prophesize(SMS::class);

        $maximumPastDateToBeNotified = (clone $this->dateTime)->modify('-' . ReminderHandler::DELAY_BETWEEN_REMIND_NOTIFICATION_IN_MINUTES . ' minutes');

        $extraDataUserOne->update(
            $maximumPastDateToBeNotified->format('Y-m-d H:i:s'),
            $this->dateTime
        )->shouldBeCalled();

        // MOCK

        $this->eventRepository->findByDay($this->dateTime)
            ->shouldBeCalled()
            ->willReturn([$eventOne->reveal(), $eventTwo->reveal()]);

        $this
            ->extraDataRepository
            ->getForEventNameOlderThanDate(
                $eventOne->reveal(),
                Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                $maximumPastDateToBeNotified
            )->shouldBeCalled()
            ->willReturn([$extraDataUserOne->reveal()]);

        $this
            ->extraDataRepository
            ->getForEventNameOlderThanDate(
                $eventTwo->reveal(),
                Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                $maximumPastDateToBeNotified
            )->shouldBeCalled()
            ->willReturn([$extraDataUserTwo->reveal()]);

        $this->userEventPhoneRepository->findValidatedByEventAndUsers(
            $eventOne->reveal(),
            [1]
        )->shouldBeCalled()->willReturn([$userEventPhoneOne->reveal()]);

        $this->userEventPhoneRepository->findValidatedByEventAndUsers(
            $eventTwo->reveal(),
            [2]
        )->shouldBeCalled()->willReturn([$userEventPhoneTwo->reveal()]);

        $this->sheetRepository
            ->getSheetsByUserAndEvent($userOne->reveal(), $eventOne->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetOne->reveal()]);

        $this->sheetRepository
            ->getSheetsByUserAndEvent($userTwo->reveal(), $eventTwo->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetTwo]);

        $this->counter->getCountAvailablePendingMeetingRequests(
            $sheetOne->reveal(),
            $participantOne->reveal()
        )->shouldBeCalled()->willReturn(10);

        $this->counter->getCountAvailablePendingMeetingRequests(
            $sheetTwo->reveal(),
            $participantTwo->reveal()
        )->shouldBeCalled()->willReturn(0);

        $this->extraDataRepository->set($extraDataUserOne->reveal())
            ->shouldBeCalled();

        $this->extraDataRepository->set($extraDataUserTwo->reveal())
            ->shouldNotBeCalled();

        $this->smsFactory->createPendingProposition(
            '+3360000000',
            $sheetOne->reveal(),
            'fr',
            10
        )->shouldBeCalled()->willReturn($smsOne->reveal());

        $this->smsSender->send($smsOne->reveal())->shouldBeCalled();

        $this->handler->handle(new Remind());
    }
}
