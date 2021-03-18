<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingRequest\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\UpdateParticipants;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\UpdateParticipantsHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingUnParticipateEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\InvalidParticipantException;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UpdateParticipantsHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $datetime      = new \DateTime();
        $event         = EventFactory::createEvent();
        $sheetFrom     = SheetFactory::create($event);
        $sheetTo       = SheetFactory::create($event);
        $user1         = UserFactory::create('emailFrom1@email.fr');
        $user2         = UserFactory::create('emailFrom2@email.fr');
        $user3         = UserFactory::create('emailTo3@email.fr');
        $user4         = UserFactory::create('emailTo4@email.fr');
        $participantF1 = ParticipantFactory::create($sheetFrom, $user1);
        $participantF2 = ParticipantFactory::create($sheetFrom, $user2);
        $participantT1 = ParticipantFactory::create($sheetTo, $user3);
        $participantT2 = ParticipantFactory::create($sheetTo, $user4);

        $request = new Request($sheetFrom, [$participantF2], $sheetTo, [$participantT2], $datetime, $user1, $event);

        // Expected
        $expectedRequest = new Request($sheetFrom, [$participantF1], $sheetTo, [$participantT1], $datetime, $user1, $event);

        // Reflection
        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participantF1, 1);
        $property->setValue($participantF2, 2);
        $property->setValue($participantT1, 3);
        $property->setValue($participantT2, 4);
        $property->setAccessible(false);

        // Mock
        $meetingRequestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRequestRepository->set($expectedRequest)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findByIds([1])->shouldBeCalled()->willReturn([$participantF1]);
        $participantRepository->findByIds([3])->shouldBeCalled()->willReturn([$participantT1]);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher
            ->dispatch(Events::MEETING_UN_PARTICIPATE, new MeetingUnParticipateEvent($participantF2))
            ->shouldBeCalled()
            ->willReturn()
        ;
        $eventDispatcher
            ->dispatch(Events::MEETING_UN_PARTICIPATE, new MeetingUnParticipateEvent($participantT2))
            ->shouldBeCalled()
            ->willReturn()
        ;
        $eventDispatcher
            ->dispatch(Events::MEETING_PARTICIPATE, new MeetingParticipateEvent($participantT1))
            ->shouldBeCalled()
            ->willReturn()
        ;
        $eventDispatcher
            ->dispatch(Events::MEETING_PARTICIPATE, new MeetingParticipateEvent($participantF1))
            ->shouldBeCalled()
            ->willReturn()
        ;

        // Handler
        $handler = new UpdateParticipantsHandler(
            $meetingRequestRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new UpdateParticipants($request, [1], [3]));
    }

    public function testHandleRemoveAll()
    {
        // Data
        $datetime      = new \DateTime();
        $event         = EventFactory::createEvent();
        $sheetFrom     = SheetFactory::create($event);
        $sheetTo       = SheetFactory::create($event);
        $user1         = UserFactory::create('emailFrom1@email.fr');
        $user2         = UserFactory::create('emailFrom2@email.fr');
        $user3         = UserFactory::create('emailTo3@email.fr');
        $user4         = UserFactory::create('emailTo4@email.fr');
        $participantF1 = ParticipantFactory::create($sheetFrom, $user1);
        $participantF2 = ParticipantFactory::create($sheetFrom, $user2);
        $participantT1 = ParticipantFactory::create($sheetTo, $user3);
        $participantT2 = ParticipantFactory::create($sheetTo, $user4);

        $request = new Request($sheetFrom, [$participantF2], $sheetTo, [$participantT2], $datetime, $user1, $event);

        // Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $datetime, $user1, $event);

        // Reflection
        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participantF1, 1);
        $property->setValue($participantF2, 2);
        $property->setValue($participantT1, 3);
        $property->setValue($participantT2, 4);
        $property->setAccessible(false);

        // Mock
        $meetingRequestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRequestRepository->set($expectedRequest)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findByIds([])->shouldBeCalled()->willReturn([]);
        $participantRepository->findByIds([])->shouldBeCalled()->willReturn([]);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher
            ->dispatch(Events::MEETING_UN_PARTICIPATE, new MeetingUnParticipateEvent($participantF2))
            ->shouldBeCalled()
            ->willReturn()
        ;
        $eventDispatcher
            ->dispatch(Events::MEETING_UN_PARTICIPATE, new MeetingUnParticipateEvent($participantT2))
            ->shouldBeCalled()
            ->willReturn()
        ;

        // Handler
        $handler = new UpdateParticipantsHandler(
            $meetingRequestRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new UpdateParticipants($request, [], []));
    }

    public function testHandleExceptionFrom()
    {
        $this->expectException(InvalidParticipantException::class);

        // Data
        $datetime      = new \DateTime();
        $event         = EventFactory::createEvent();
        $sheetFrom     = SheetFactory::create($event);
        $sheetTo       = SheetFactory::create($event);
        $sheetUnknown  = SheetFactory::create($event);
        $user1         = UserFactory::create('emailFrom1@email.fr');
        $user2         = UserFactory::create('emailFrom2@email.fr');
        $user3         = UserFactory::create('emailTo3@email.fr');
        $user4         = UserFactory::create('emailTo4@email.fr');
        $userUnknown   = UserFactory::create('emailUnknown@email.fr');
        $participantF1 = ParticipantFactory::create($sheetFrom, $user1);
        $participantF2 = ParticipantFactory::create($sheetFrom, $user2);
        $participantT1 = ParticipantFactory::create($sheetTo, $user3);
        $participantT2 = ParticipantFactory::create($sheetTo, $user4);
        $participantUnknown = ParticipantFactory::create($sheetUnknown, $userUnknown);

        $request = new Request($sheetFrom, [$participantF2], $sheetTo, [$participantT2], $datetime, $user1, $event);

        // Reflection
        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participantF1, 1);
        $property->setValue($participantF2, 2);
        $property->setValue($participantT1, 3);
        $property->setValue($participantT2, 4);
        $property->setValue($participantUnknown, 5);
        $property->setAccessible(false);

        // Mock
        $meetingRequestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRequestRepository->set($request)->shouldNotBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findByIds([5])->shouldBeCalled()->willReturn([$participantUnknown]);
        $participantRepository->findByIds([3])->shouldBeCalled()->willReturn([$participantT1]);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Handler
        $handler = new UpdateParticipantsHandler(
            $meetingRequestRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new UpdateParticipants($request, [5], [3]));
    }

    public function testHandleExceptionTo()
    {
        $this->expectException(InvalidParticipantException::class);

        // Data
        $datetime      = new \DateTime();
        $event         = EventFactory::createEvent();
        $sheetFrom     = SheetFactory::create($event);
        $sheetTo       = SheetFactory::create($event);
        $sheetUnknown  = SheetFactory::create($event);
        $user1         = UserFactory::create('emailFrom1@email.fr');
        $user2         = UserFactory::create('emailFrom2@email.fr');
        $user3         = UserFactory::create('emailTo3@email.fr');
        $user4         = UserFactory::create('emailTo4@email.fr');
        $userUnknown   = UserFactory::create('emailUnknown@email.fr');
        $participantF1 = ParticipantFactory::create($sheetFrom, $user1);
        $participantF2 = ParticipantFactory::create($sheetFrom, $user2);
        $participantT1 = ParticipantFactory::create($sheetTo, $user3);
        $participantT2 = ParticipantFactory::create($sheetTo, $user4);
        $participantUnknown = ParticipantFactory::create($sheetUnknown, $userUnknown);

        $request = new Request($sheetFrom, [$participantF2], $sheetTo, [$participantT2], $datetime, $user1, $event);

        // Reflection
        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participantF1, 1);
        $property->setValue($participantF2, 2);
        $property->setValue($participantT1, 3);
        $property->setValue($participantT2, 4);
        $property->setValue($participantUnknown, 5);
        $property->setAccessible(false);

        // Mock
        $meetingRequestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRequestRepository->set($request)->shouldNotBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findByIds([1])->shouldBeCalled()->willReturn([$participantF1]);
        $participantRepository->findByIds([5])->shouldBeCalled()->willReturn([$participantUnknown]);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Handler
        $handler = new UpdateParticipantsHandler(
            $meetingRequestRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new UpdateParticipants($request, [1], [5]));
    }
}
