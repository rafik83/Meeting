<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\Agenda;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Agenda\ConfirmationCalculator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class ConfirmationCalculatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $userEventTokenRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $happeningParticipationRepository;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
    }

    public function testGetConfirmationStatusForSheetAgendaNotConcerned()
    {
        // Context
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $this->sheet->getParticipants()->willReturn(new ArrayCollection([$participant->reveal()]));
        $participant->getUser()->willReturn($user->reveal());

        // Expected
        $this->userEventTokenRepository
            ->getForEventTypeAndUsers($this->event->reveal(), UserEventTokenType::AGENDA_CONFIRMATION, [$user->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        // Confirmation Calculator
        $confirmationCalculator = new ConfirmationCalculator(
            $this->userEventTokenRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->happeningParticipationRepository->reveal()
        );

        $result = $confirmationCalculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = Sheet::AGENDA_NOT_CONCERNED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAgendaNoneConfirmed()
    {
        // Context
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $this->sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([$participant1->reveal(), $participant2->reveal()]));

        $token1 = $this->prophesize(UserEventToken::class);
        $token2 = $this->prophesize(UserEventToken::class);

        // Expected
        $this->userEventTokenRepository
            ->getForEventTypeAndUsers(
                $this->event->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION,
                [$user1->reveal(), $user2->reveal()]
            )->shouldBeCalled()
            ->willReturn([$token1->reveal(), $token2->reveal()])
        ;
        $token1->getUser()->willReturn($user1->reveal());
        $token2->getUser()->willReturn($user2->reveal());

        $this->meetingRepository
            ->hasMeetingForUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false);
        $this->meetingRepository
            ->hasMeetingForUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldNotBeCalled();

        $token1->isConfirmed()->shouldBeCalled()->willReturn(false);
        $token2->isConfirmed()->shouldBeCalled()->willReturn(false);

        // Confirmation Calculator
        $confirmationCalculator = new ConfirmationCalculator(
            $this->userEventTokenRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->happeningParticipationRepository->reveal()
        );

        $result = $confirmationCalculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = Sheet::AGENDA_NONE_CONFIRMED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAgendaPartlyConfirmed()
    {
        // Context
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $this->sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([$participant1->reveal(), $participant2->reveal()]));

        $token1 = $this->prophesize(UserEventToken::class);
        $token2 = $this->prophesize(UserEventToken::class);

        // Expected
        $this->userEventTokenRepository
            ->getForEventTypeAndUsers(
                $this->event->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION,
                [$user1->reveal(), $user2->reveal()]
            )->shouldBeCalled()
            ->willReturn([$token1->reveal(), $token2->reveal()])
        ;
        $token1->getUser()->willReturn($user1->reveal());
        $token2->getUser()->willReturn($user2->reveal());

        $this->meetingRepository
            ->hasMeetingForUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false);
        $this->meetingRepository
            ->hasMeetingForUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldNotBeCalled();

        $token1->isConfirmed()->shouldBeCalled()->willReturn(true);
        $token2->isConfirmed()->shouldBeCalled()->willReturn(false);

        // Confirmation Calculator
        $confirmationCalculator = new ConfirmationCalculator(
            $this->userEventTokenRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->happeningParticipationRepository->reveal()
        );

        $result = $confirmationCalculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = Sheet::AGENDA_PARTLY_CONFIRMED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAgendaFullyConfirmed()
    {
        // Context
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $this->sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([$participant1->reveal(), $participant2->reveal()]));

        $token1 = $this->prophesize(UserEventToken::class);
        $token2 = $this->prophesize(UserEventToken::class);

        // Expected
        $this->userEventTokenRepository
            ->getForEventTypeAndUsers(
                $this->event->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION,
                [$user1->reveal(), $user2->reveal()]
            )->shouldBeCalled()
            ->willReturn([$token1->reveal(), $token2->reveal()])
        ;
        $token1->getUser()->willReturn($user1->reveal());
        $token2->getUser()->willReturn($user2->reveal());

        $this->meetingRepository
            ->hasMeetingForUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false);
        $this->meetingRepository
            ->hasMeetingForUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldNotBeCalled();

        $token1->isConfirmed()->shouldBeCalled()->willReturn(true);
        $token2->isConfirmed()->shouldBeCalled()->willReturn(true);

        // Confirmation Calculator
        $confirmationCalculator = new ConfirmationCalculator(
            $this->userEventTokenRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->happeningParticipationRepository->reveal()
        );

        $result = $confirmationCalculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = Sheet::AGENDA_ALL_CONFIRMED;

        $this->assertEquals($expected, $result);
    }
}
