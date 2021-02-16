<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\Phone;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationCalculator;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationStatus;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class ValidationCalculatorTest extends TestCase
{
    public function testGetValidationStatusForSheetNotConcerned()
    {
        $event = $this->prophesize(Event::class);
        $type  = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);

        $sheet->getType()->willReturn($type->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $type->getId()->willReturn(124);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tipRepository
            ->isConfirmationPhoneEnabled($event->reveal(), $type->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $calculator = new ValidationCalculator(
            $tipRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $meetingRepository->reveal()
        );

        $result = $calculator->getValidationStatusForSheet($sheet->reveal());

        $this->assertEquals(ValidationStatus::NOT_CONCERNED, $result);
    }

    public function testGetValidationStatusForSheet()
    {
        $event = $this->prophesize(Event::class);
        $type  = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $participant3->getUser()->willReturn($user3->reveal());
        $userEventPhone1 = $this->prophesize(User\UserEventPhone::class);
        $userEventPhone2 = $this->prophesize(User\UserEventPhone::class);
        $userEventPhone1->isValidated()->willReturn(false);
        $userEventPhone2->isValidated()->willReturn(true);
        $sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal(),
            ]))
        ;

        $sheet->getType()->willReturn($type->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $type->getId()->willReturn(124);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tipRepository
            ->isConfirmationPhoneEnabled($event->reveal(), $type->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->hasMeetingForUserAndEvent($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $meetingRepository
            ->hasMeetingForUserAndEvent($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $meetingRepository
            ->hasMeetingForUserAndEvent($user3->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone1->reveal())
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone2->reveal())
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user3->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $calculator = new ValidationCalculator(
            $tipRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $meetingRepository->reveal()
        );

        $result = $calculator->getValidationStatusForSheet($sheet->reveal());

        $this->assertEquals(ValidationStatus::PARTLY_CONFIRMED, $result);
    }

    public function testGetValidationStatusForSheetNoneConfirmed()
    {
        $event = $this->prophesize(Event::class);
        $type  = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $userEventPhone1 = $this->prophesize(User\UserEventPhone::class);
        $userEventPhone2 = $this->prophesize(User\UserEventPhone::class);
        $userEventPhone1->isValidated()->willReturn(false);
        $userEventPhone2->isValidated()->willReturn(false);
        $sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([
                $participant1->reveal(),
                $participant2->reveal(),
            ]))
        ;

        $sheet->getType()->willReturn($type->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $type->getId()->willReturn(124);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tipRepository
            ->isConfirmationPhoneEnabled($event->reveal(), $type->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->hasMeetingForUserAndEvent($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $meetingRepository
            ->hasMeetingForUserAndEvent($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone1->reveal())
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone2->reveal())
        ;

        $calculator = new ValidationCalculator(
            $tipRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $meetingRepository->reveal()
        );

        $result = $calculator->getValidationStatusForSheet($sheet->reveal());

        $this->assertEquals(ValidationStatus::NONE_CONFIRMED, $result);
    }

    public function testGetValidationStatusForSheetAllConfirmed()
    {
        $event = $this->prophesize(Event::class);
        $type  = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $userEventPhone1 = $this->prophesize(User\UserEventPhone::class);
        $userEventPhone2 = $this->prophesize(User\UserEventPhone::class);
        $userEventPhone1->isValidated()->willReturn(true);
        $userEventPhone2->isValidated()->willReturn(false);
        $sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([
                $participant1->reveal(),
                $participant2->reveal(),
            ]))
        ;

        $sheet->getType()->willReturn($type->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $type->getId()->willReturn(124);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tipRepository
            ->isConfirmationPhoneEnabled($event->reveal(), $type->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->hasMeetingForUserAndEvent($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $meetingRepository
            ->hasMeetingForUserAndEvent($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone1->reveal())
        ;
        $userEventPhoneChecker
            ->getValidatedUserEventPhone($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone2->reveal())
        ;

        $calculator = new ValidationCalculator(
            $tipRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $meetingRepository->reveal()
        );

        $result = $calculator->getValidationStatusForSheet($sheet->reveal());

        $this->assertEquals(ValidationStatus::ALL_CONFIRMED, $result);
    }
}
