<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaDayViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaDayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaDayView;
use Proximum\Vimeet\Application\View\Agenda\AgendaParticipantView;
use Proximum\Vimeet\Domain\KeyDates\Checker\SmsActivationDateAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class AgendaParticipantViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $dayRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $agendaDayViewQueryHandler;

    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    /** @var ObjectProphecy */
    private $smsActivationDateAccessChecker;

    /** @var ObjectProphecy */
    private $userEventPhoneRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $participant;

    public function setUp()
    {
        $this->sheet                          = $this->prophesize(Sheet::class);
        $this->participant                    = $this->prophesize(Participant::class);
        $this->event                          = $this->prophesize(Event::class);
        $this->dayRepository                  = $this->prophesize(DayRepositoryInterface::class);
        $this->meetingRepository              = $this->prophesize(MeetingRepositoryInterface::class);
        $this->agendaDayViewQueryHandler      = $this->prophesize(AgendaDayViewQueryHandler::class);
        $this->participantInfoGuesser         = $this->prophesize(ParticipantInfoGuesser::class);
        $this->smsActivationDateAccessChecker = $this->prophesize(SmsActivationDateAccessChecker::class);
        $this->userEventPhoneRepository       = $this->prophesize(UserEventPhoneRepositoryInterface::class);
    }

    public function testHandle()
    {
        $user = $this->prophesize(User::class);
        $day1 = $this->prophesize(Day::class);
        $day2 = $this->prophesize(Day::class);
        $dayView1 = $this->prophesize(AgendaDayView::class);
        $dayView2 = $this->prophesize(AgendaDayView::class);
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $unavailabilitie = $this->prophesize(Unavailability::class);
        $mass = $this->prophesize(Unavailability\Mass::class);
        $meeting = $this->prophesize(Meeting::class);
        $massAssignment = $this->prophesize(Unavailability\MassAssignment::class);
        $userEventPhone = $this->prophesize(User\UserEventPhone::class);
        $meeting1 = $this->prophesize(Meeting::class);
        $this->participant->getUser()->willReturn($user->reveal());
        $this->participant->getId()->willReturn(123);
        $userEventPhone->isStop()->shouldBeCalled()->willReturn(false);
        $userEventPhone->isValidated()->shouldBeCalled()->willReturn(true);
        $user->getEmail()->willReturn('email@example.net');
        $this->sheet->attend()->willReturn(true);

        // Expected
        $this->dayRepository
            ->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([1 => $day1->reveal(), 2 => $day2->reveal()])
        ;

        $this
            ->meetingRepository
            ->findByUserAndEventExceptSheet($user->reveal(), $this->event->reveal(), $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$meeting1->reveal()])
        ;

        $this->agendaDayViewQueryHandler
            ->handle(new AgendaDayViewQuery(
                $this->sheet->reveal(),
                $day1->reveal(),
                1,
                $this->participant->reveal(),
                'fr',
                [$happeningParticipation->reveal()],
                [$unavailabilitie->reveal()],
                [$mass->reveal()],
                [$meeting->reveal()],
                [$massAssignment->reveal()],
                [$meeting1->reveal()]
            ))
            ->shouldBeCalled()
            ->willReturn($dayView1->reveal())
        ;

        $this->agendaDayViewQueryHandler
            ->handle(new AgendaDayViewQuery(
                $this->sheet->reveal(),
                $day2->reveal(),
                2,
                $this->participant->reveal(),
                'fr',
                [$happeningParticipation->reveal()],
                [$unavailabilitie->reveal()],
                [$mass->reveal()],
                [$meeting->reveal()],
                [$massAssignment->reveal()],
                [$meeting1->reveal()]
            ))
            ->shouldBeCalled()
            ->willReturn($dayView2->reveal())
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Participant CompleteName')
        ;

        $this->userEventPhoneRepository
            ->find($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone->reveal())
        ;
        $this->smsActivationDateAccessChecker
            ->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        // Query Handler
        $handler = new AgendaParticipantViewQueryHandler(
            $this->dayRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->agendaDayViewQueryHandler->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->smsActivationDateAccessChecker->reveal(),
            $this->userEventPhoneRepository->reveal()
        );
        $result = $handler->handle(new AgendaParticipantViewQuery(
            $this->participant->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            'fr',
            [$happeningParticipation->reveal()],
            [$unavailabilitie->reveal()],
            [$mass->reveal()],
            [$meeting->reveal()],
            [$massAssignment->reveal()]
        ));

        $expected = new AgendaParticipantView(
            123,
            'Participant CompleteName',
            'email@example.net',
            [$dayView1->reveal(), $dayView2->reveal()],
            true,
            true,
            true,
            false
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleStopSMS()
    {
        $user = $this->prophesize(User::class);
        $day1 = $this->prophesize(Day::class);
        $dayView1 = $this->prophesize(AgendaDayView::class);
        $userEventPhone = $this->prophesize(User\UserEventPhone::class);
        $this->participant->getUser()->willReturn($user->reveal());
        $this->participant->getId()->willReturn(123);
        $userEventPhone->isStop()->shouldBeCalled()->willReturn(true);
        $userEventPhone->isValidated()->shouldBeCalled()->willReturn(false);
        $user->getEmail()->willReturn('email@example.net');
        $this->sheet->attend()->willReturn(true);

        // Expected
        $this->dayRepository->findByEvent($this->event->reveal())->shouldBeCalled()->willReturn([1 => $day1->reveal()]);

        $this
            ->meetingRepository
            ->findByUserAndEventExceptSheet($user->reveal(), $this->event->reveal(), $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->agendaDayViewQueryHandler
            ->handle(new AgendaDayViewQuery(
                $this->sheet->reveal(),
                $day1->reveal(),
                1,
                $this->participant->reveal(),
                'fr',
                [],
                [],
                [],
                [],
                [],
                []
            ))
            ->shouldBeCalled()
            ->willReturn($dayView1->reveal())
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Participant CompleteName')
        ;

        $this->userEventPhoneRepository
            ->find($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone->reveal())
        ;
        $this->smsActivationDateAccessChecker
            ->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        // Query Handler
        $handler = new AgendaParticipantViewQueryHandler(
            $this->dayRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->agendaDayViewQueryHandler->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->smsActivationDateAccessChecker->reveal(),
            $this->userEventPhoneRepository->reveal()
        );
        $result = $handler->handle(new AgendaParticipantViewQuery(
            $this->participant->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            'fr',
            [],
            [],
            [],
            [],
            []
        ));

        $expected = new AgendaParticipantView(
            123,
            'Participant CompleteName',
            'email@example.net',
            [$dayView1->reveal()],
            true,
            false,
            false,
            true
        );

        $this->assertEquals($expected, $result);
    }
}
