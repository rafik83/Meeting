<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\UpdateMeetingRequest;
use Proximum\Vimeet\Application\Command\Meeting\UpdateMeetingRequestHandler;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUpdateMeetingRequestException;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateMeetingRequestHandlerTest extends TestCase
{
    public function testHandleWithStateSentForSheetFrom()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');
        $user4     = new User('email@email.com', 'salt', 'password', 'fr');
        $datetime  = new \DateTime('2016-01-24 09:00:00');
        $sheetTo   = new Sheet($event, $type, [], $user1, $datetime);
        $sheetFrom = new Sheet($event, $type, [], $user4, $datetime);

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetFrom, $user3, 3);
        $sheetFrom->addParticipant($participant1);
        $sheetFrom->addParticipant($participant2);
        $sheetFrom->addParticipant($participant3);

        //Actual
        $request = new Request($sheetFrom, [$participant1, $participant2], $sheetTo, [], $datetime, $user1, $event);

        //Command
        $command = new UpdateMeetingRequest($request, $sheetFrom);
        $command->participants = [$participant1, $participant3];
        $command->isPriority = false;
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [0 => $participant1, 2 => $participant3], $sheetTo, [], $datetime, $user1, $event, false, true, false , false);
        $expectedMessage = new Message($request, $sheetFrom, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToEditSentOrApproved($request, $sheetFrom)->shouldBeCalled()->willReturn(true);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        //Handler
        $handler = new UpdateMeetingRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $datetime,
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithStateApprovedForSheetFrom()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');
        $user4     = new User('email@email.com', 'salt', 'password', 'fr');
        $datetime  = new \DateTime('2016-01-24 09:00:00');
        $sheetTo   = new Sheet($event, $type, [], $user1, $datetime);
        $sheetFrom = new Sheet($event, $type, [], $user4, $datetime);

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetFrom, $user3, 3);
        $sheetFrom->addParticipant($participant1);
        $sheetFrom->addParticipant($participant2);
        $sheetFrom->addParticipant($participant3);

        //Actual
        $request = new Request($sheetFrom, [$participant1, $participant2], $sheetTo, [], $datetime, $user1, $event);
        $request->approve($datetime);

        //Command
        $command = new UpdateMeetingRequest($request, $sheetFrom);
        $command->participants = [$participant1, $participant3];
        $command->isPriority = false;
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [0 => $participant1, 2 => $participant3], $sheetTo, [], $datetime, $user1, $event, false, true, false, false);
        $expectedRequest->approve($datetime);
        $expectedMessage = new Message($request, $sheetFrom, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToEditSentOrApproved($request, $sheetFrom)->shouldBeCalled()->willReturn(true);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        //Handler
        $handler = new UpdateMeetingRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $datetime,
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithStateSentForSheetTo()
    {
        $this->expectException(IsNotAllowedToUpdateMeetingRequestException::class);

        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');
        $user4     = new User('email@email.com', 'salt', 'password', 'fr');
        $datetime  = new \DateTime('2016-01-24 09:00:00');
        $sheetTo   = new Sheet($event, $type, [], $user1, $datetime);
        $sheetFrom = new Sheet($event, $type, [], $user4, $datetime);

        $participant1 = $this->createParticipantMock($sheetTo, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetTo, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetTo, $user3, 3);
        $sheetTo->addParticipant($participant1);
        $sheetTo->addParticipant($participant2);
        $sheetTo->addParticipant($participant3);

        //Actual
        $request = new Request($sheetFrom, [], $sheetTo, [$participant1, $participant2], $datetime, $user1, $event);

        //Command
        $command = new UpdateMeetingRequest($request, $sheetTo);
        $command->participants = [$participant1, $participant3];
        $command->isPriority = false;
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [0 => $participant1, 2 => $participant3], $datetime, $user1, $event, false, true, false, false);
        $expectedMessage = new Message($request, $sheetTo, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldNotBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldNotBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToEditSentOrApproved($request, $sheetTo)->shouldBeCalled()->willReturn(false);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        //Handler
        $handler = new UpdateMeetingRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $datetime,
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithStateApprovedForSheetTo()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');
        $user4     = new User('email@email.com', 'salt', 'password', 'fr');
        $datetime  = new \DateTime('2016-01-24 09:00:00');
        $sheetTo   = new Sheet($event, $type, [], $user1, $datetime);
        $sheetFrom = new Sheet($event, $type, [], $user4, $datetime);

        $participant1 = $this->createParticipantMock($sheetTo, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetTo, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetTo, $user3, 3);
        $sheetTo->addParticipant($participant1);
        $sheetTo->addParticipant($participant2);
        $sheetTo->addParticipant($participant3);

        //Actual
        $request = new Request($sheetFrom, [], $sheetTo, [$participant1, $participant2], $datetime, $user1, $event);
        $request->approve($datetime);

        //Command
        $command = new UpdateMeetingRequest($request, $sheetTo);
        $command->participants = [$participant1, $participant3];
        $command->isPriority = true;
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [0 => $participant1, 2 => $participant3], $datetime, $user1, $event, false, true, false, true);
        $expectedRequest->approve($datetime);
        $expectedMessage = new Message($request, $sheetTo, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToEditSentOrApproved($request, $sheetTo)->shouldBeCalled()->willReturn(true);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        //Handler
        $handler = new UpdateMeetingRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $datetime,
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithStateApprovedForSheetToWithoutMessage()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');
        $user4     = new User('email@email.com', 'salt', 'password', 'fr');
        $datetime  = new \DateTime('2016-01-24 09:00:00');
        $sheetTo   = new Sheet($event, $type, [], $user1, $datetime);
        $sheetFrom = new Sheet($event, $type, [], $user4, $datetime);

        $participant1 = $this->createParticipantMock($sheetTo, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetTo, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetTo, $user3, 3);
        $sheetTo->addParticipant($participant1);
        $sheetTo->addParticipant($participant2);
        $sheetTo->addParticipant($participant3);

        //Actual
        $request = new Request($sheetFrom, [], $sheetTo, [$participant1, $participant2], $datetime, $user1, $event);
        $request->approve($datetime);

        //Command
        $command = new UpdateMeetingRequest($request, $sheetTo);
        $command->isPriority = true;
        $command->participants = [$participant1, $participant3];

        //Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [0 => $participant1, 2 => $participant3], $datetime, $user1, $event, false, false, false, true);
        $expectedRequest->approve($datetime);
        $expectedMessage = new Message($expectedRequest, $sheetTo, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldNotBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToEditSentOrApproved($request, $sheetTo)->shouldBeCalled()->willReturn(true);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        //Handler
        $handler = new UpdateMeetingRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $datetime,
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param $id
     *
     * @return Participant
     */
    public function createParticipantMock(Sheet $sheet, User $user, $id)
    {
        $participant = new Participant($sheet, $user, [], false, new \DateTime());
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
    }
}
