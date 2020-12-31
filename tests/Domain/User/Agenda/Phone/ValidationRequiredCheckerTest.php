<?php

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
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ValidationRequiredCheckerTest extends TestCase
{
    /** @var ConfirmationPhoneTipChecker */
    private $confirmationPhoneTipChecker;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var Type */
    private $type;

    /** @var Sheet */
    private $sheet;

    public function setUp()
    {
        $this->confirmationPhoneTipChecker = $this->prophesize(ConfirmationPhoneTipChecker::class);
        $this->userEventPhoneChecker       = $this->prophesize(UserEventPhoneChecker::class);
        $this->meetingRepository           = $this->prophesize(MeetingRepositoryInterface::class);
        $this->datetime                    = new \DateTime();

        $this->validationRequiredChecker = new ValidationRequiredChecker(
            $this->confirmationPhoneTipChecker->reveal(),
            $this->userEventPhoneChecker->reveal(),
            $this->meetingRepository->reveal(),
            $this->datetime
        );

        $this->user  = UserFactory::create();
        $this->event = $this->prophesize(Event::class);
        $this->type  = new Type($this->event->reveal());

        $this->sheet = SheetFactory::create($this->event->reveal());
        $this->sheet->updateType($this->type);
    }

    public function testHandleTipConfirmationPhoneDisabled()
    {
        $this->confirmationPhoneTipChecker->isEnabled(
            $this->event->reveal(),
            $this->type
        )->shouldBeCalled()->willReturn(false);

        $this->userEventPhoneChecker->isValidated(
            $this->user,
            $this->event->reveal()
        )->shouldNotBeCalled();

        $this->meetingRepository->hasMeetingForUserAndEvent($this->user, $this->event)->shouldNotBeCalled();

        $validationRequired = $this->validationRequiredChecker->handle($this->sheet, $this->user);
        $this->assertEquals(false, $validationRequired);
    }

    public function testHandleUserHasNoMeeting()
    {
        $this->confirmationPhoneTipChecker->isEnabled(
            $this->event->reveal(),
            $this->type
        )->shouldBeCalled()->willReturn(true);

        $this->meetingRepository->hasMeetingForUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->userEventPhoneChecker->isValidated(
            $this->user,
            $this->event->reveal()
        )->shouldNotBeCalled();

        $validationRequired = $this->validationRequiredChecker->handle($this->sheet, $this->user);
        $this->assertEquals(false, $validationRequired);
    }

    public function testHandleAgendaOnlineDateNotPassed()
    {
        $this->confirmationPhoneTipChecker->isEnabled(
            $this->event->reveal(),
            $this->type
        )->shouldBeCalled()->willReturn(true);

        $this->meetingRepository->hasMeetingForUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(true);

        $cloneDatetime = clone $this->datetime;
        $datetimeInFuture = $cloneDatetime->modify('+1 day');

        $configuration = (new Configuration('leftColor', 'rightColor', 'textColor'))->setDates(
            null,
            null,
            null,
            null,
            null,
            null,
            $datetimeInFuture
        );

        $this->event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $validationRequired = $this->validationRequiredChecker->handle($this->sheet, $this->user);

        $this->assertEquals(false, $validationRequired);
    }

    public function testHandleAllConditionsValidated()
    {
        $cloneDatetime = clone $this->datetime;
        $oldDatetime = $cloneDatetime->modify('-1 day');

        $configuration = (new Configuration('leftColor', 'rightColor', 'textColor'))->setDates(
            null,
            null,
            null,
            null,
            null,
            null,
            $oldDatetime
        );

        $this->event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $this->meetingRepository->hasMeetingForUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->confirmationPhoneTipChecker->isEnabled(
            $this->event->reveal(),
            $this->type
        )->shouldBeCalled()->willReturn(true);

        $this->userEventPhoneChecker->isValidated(
            $this->user,
            $this->event->reveal()
        )->shouldBeCalled()->willReturn(false);

        $validationRequired = $this->validationRequiredChecker->handle($this->sheet, $this->user);

        $this->assertEquals(true, $validationRequired);
    }
}
