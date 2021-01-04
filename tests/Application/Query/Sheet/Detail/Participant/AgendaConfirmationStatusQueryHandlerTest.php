<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Detail\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AgendaConfirmationStatusQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AgendaConfirmationStatusQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationNotConcernedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationNotSentView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaNotConfirmedView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class AgendaConfirmationStatusQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $userEventTokenRepository;

    /** @var ObjectProphecy */
    private $happeningParticipationRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var AgendaConfirmationStatusQuery */
    private $agendaConfirmationStatusQuery;

    /** @var AgendaConfirmationStatusQueryHandler */
    private $agendaConfirmationStatusQueryHandler;

    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var UserEventToken */
    private $userEventToken;

    /** @var \DateTime */
    private $dateTime;

    public function setUp()
    {
        $this->userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $this->event       = EventFactory::createEvent();
        $this->user        = UserFactory::create();
        $this->sheet       = SheetFactory::create();
        $this->participant = ParticipantFactory::create($this->sheet, $this->user);
        $this->dateTime    = new \DateTime();
        $this->userEventToken = new UserEventToken($this->event, $this->user, 'type', 'token', $this->dateTime);

        $this->agendaConfirmationStatusQuery = new AgendaConfirmationStatusQuery(
            $this->participant,
            $this->event
        );

        $this->agendaConfirmationStatusQueryHandler = new AgendaConfirmationStatusQueryHandler(
            $this->userEventTokenRepository->reveal(),
            $this->happeningParticipationRepository->reveal(),
            $this->meetingRepository->reveal()
        );
    }

    public function testHandleWithConfirmedAgenda()
    {
        $expected = new AgendaConfirmedView();

        $this->userEventToken->confirm($this->dateTime);

        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, UserEventTokenType::AGENDA_CONFIRMATION)
            ->shouldBeCalled()
            ->willReturn($this->userEventToken);

        $this->happeningParticipationRepository
            ->checkAnyParticipation($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->meetingRepository
            ->hasScheduledMeetingByParticipant($this->participant)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $result = $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);

        $this->assertEquals($result, $expected);
    }

    public function testHandleWithNotConfirmedAgenda()
    {
        $expected = new AgendaNotConfirmedView();

        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, UserEventTokenType::AGENDA_CONFIRMATION)
            ->shouldBeCalled()
            ->willReturn($this->userEventToken)
        ;

        $this->happeningParticipationRepository
            ->checkAnyParticipation($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->meetingRepository
            ->hasScheduledMeetingByParticipant($this->participant)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $result = $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);

        $this->assertEquals($result, $expected);

        $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);
    }

    public function testHandleWithNotSentConfirmationWithMeeting()
    {
        $expected = new AgendaConfirmationNotSentView();

        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, UserEventTokenType::AGENDA_CONFIRMATION)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->happeningParticipationRepository
            ->checkAnyParticipation($this->participant->getUser(), $this->event)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->meetingRepository
            ->hasScheduledMeetingByParticipant($this->participant)
            ->shouldBeCalled()
            ->willReturn(true);

        $result = $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);

        $this->assertEquals($result, $expected);
    }

    public function testHandleWithNotSentConfirmationWithHappening()
    {
        $expected = new AgendaConfirmationNotSentView();

        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, UserEventTokenType::AGENDA_CONFIRMATION)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->happeningParticipationRepository
            ->checkAnyParticipation($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(1);

        $result = $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);

        $this->assertEquals($result, $expected);
    }

    public function testHandleWithNotConcernedUser()
    {
        $expected = new AgendaConfirmationNotConcernedView();

        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, UserEventTokenType::AGENDA_CONFIRMATION)
            ->shouldNotBeCalled()
        ;

        $this->happeningParticipationRepository
            ->checkAnyParticipation($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->meetingRepository
            ->hasScheduledMeetingByParticipant($this->participant)
            ->shouldBeCalled()
            ->willReturn(false);

        $result = $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);

        $this->assertEquals($result, $expected);
        $this->agendaConfirmationStatusQueryHandler->handle($this->agendaConfirmationStatusQuery);
    }
}
