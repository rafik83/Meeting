<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Meeting\AddParticipantToMeeting;
use Proximum\Vimeet\Application\Command\Meeting\AddParticipantToMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;

class AddParticipantToMeetingHandlerTest extends TestCase
{
    /** @var ObjectProphecy|MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var ObjectProphecy|ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ObjectProphecy|DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var AddParticipantToMeetingHandler */
    private $addParticipantToMeetingHandler;

    protected function setUp()
    {
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $this->addParticipantToMeetingHandler = new AddParticipantToMeetingHandler(
            $this->meetingRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    public function test_meeting_has_already_the_given_participant()
    {
        $fromSheet = $this->prophesize(Sheet::class);
        $toSheet = $this->prophesize(Sheet::class);
        $participant = ParticipantFactory::create($toSheet->reveal());
        $meeting = MeetingFactory::createMeeting($fromSheet->reveal(), $toSheet->reveal(), null, [], [$participant]);

        $this->participantRepository->isAvailableForMeeting([$participant], $meeting)->shouldNotBeCalled();

        $this->addParticipantToMeetingHandler->handle(new AddParticipantToMeeting($participant, $meeting));
    }

    public function test_participant_not_available()
    {
        $fromSheet = $this->prophesize(Sheet::class);
        $toSheet = $this->prophesize(Sheet::class);
        $participant = ParticipantFactory::create($toSheet->reveal());
        $meeting = MeetingFactory::createMeeting($fromSheet->reveal(), $toSheet->reveal(), null, [], []);

        $this->participantRepository
            ->isAvailableForMeeting([$participant], $meeting)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->meetingRepository->set($meeting)->shouldNotBeCalled();

        $this->addParticipantToMeetingHandler->handle(new AddParticipantToMeeting($participant, $meeting));
    }

    public function test_participant_is_available()
    {
        $fromSheet = $this->prophesize(Sheet::class);
        $toSheet = $this->prophesize(Sheet::class);
        $participant = ParticipantFactory::create($toSheet->reveal());
        $meeting = MeetingFactory::createMeeting($fromSheet->reveal(), $toSheet->reveal(), null, [], []);

        $this->participantRepository
            ->isAvailableForMeeting([$participant], $meeting)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->meetingRepository->set($meeting)->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(
                Events::MEETING_PARTICIPATE,
                new MeetingParticipateEvent($participant)
            )
            ->shouldBeCalled()
        ;

        $this->addParticipantToMeetingHandler->handle(new AddParticipantToMeeting($participant, $meeting));
    }
}
