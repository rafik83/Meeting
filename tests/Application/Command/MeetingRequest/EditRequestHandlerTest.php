<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestFrom;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestTo;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestFromHandler;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestToHandler;
use Proximum\Vimeet\Application\Event\MeetingRequest\MessageEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantRemovedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EditRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithStateSentForSheetFrom()
    {
        // Context
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetFrom, $user3, 3);
        $sheetFrom->addParticpant($participant1);
        $sheetFrom->addParticpant($participant2);
        $sheetFrom->addParticpant($participant3);

        $datetime = new \DateTime('2016-01-24 09:00:00');

        //Actual
        $request = new Request($sheetFrom, [$participant1, $participant2], $sheetTo, [], $datetime, $user1);

        //Command
        $command = new UpdateRequestFrom($request, $datetime, $user1);
        $command->participants = [$participant1, $participant3];
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [0 => $participant1, 2 => $participant3], $sheetTo, [], $datetime, $user1);
        $expectedMessage = new Message($expectedRequest, $sheetFrom, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent($user2, $participant2, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.participant.added',
            new ParticipantAddedEvent($user3, $participant3, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.update.message',
            new MessageEvent($expectedMessage, $user1)
        )->shouldBeCalled();

        //Handler
        $handler = new UpdateRequestFromHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command, $sheetFrom);
    }

    public function testHandleWithStateApprovedForSheetFrom()
    {
        // Context
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetFrom, $user3, 3);
        $sheetFrom->addParticpant($participant1);
        $sheetFrom->addParticpant($participant2);
        $sheetFrom->addParticpant($participant3);

        $datetime = new \DateTime('2016-01-24 09:00:00');

        //Actual
        $request = new Request($sheetFrom, [$participant1, $participant2], $sheetTo, [], $datetime, $user1);
        $request->approve($datetime);

        //Command
        $command = new UpdateRequestFrom($request, $datetime, $user1);
        $command->participants = [$participant1, $participant3];
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [0 => $participant1, 2 => $participant3], $sheetTo, [], $datetime, $user1);
        $expectedRequest->approve($datetime);
        $expectedMessage = new Message($expectedRequest, $sheetFrom, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent($user2, $participant2, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.participant.added',
            new ParticipantAddedEvent($user3, $participant3, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.update.message',
            new MessageEvent($expectedMessage, $user1)
        )->shouldBeCalled();

        //Handler
        $handler = new UpdateRequestFromHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command, $sheetFrom);
    }

    public function testHandleWithStateSentForSheetTo()
    {
        // Context
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');

        $participant1 = $this->createParticipantMock($sheetTo, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetTo, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetTo, $user3, 3);
        $sheetTo->addParticpant($participant1);
        $sheetTo->addParticpant($participant2);
        $sheetTo->addParticpant($participant3);

        $datetime = new \DateTime('2016-01-24 09:00:00');

        //Actual
        $request = new Request($sheetFrom, [], $sheetTo, [$participant1, $participant2], $datetime, $user1);

        //Command
        $command = new UpdateRequestTo($request, $datetime, $user1);
        $command->participants = [$participant1, $participant3];
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [0 => $participant1, 2 => $participant3], $datetime, $user1);
        $expectedMessage = new Message($expectedRequest, $sheetTo, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent($user2, $participant2, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.participant.added',
            new ParticipantAddedEvent($user3, $participant3, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.update.message',
            new MessageEvent($expectedMessage, $user1)
        )->shouldBeCalled();

        //Handler
        $handler = new UpdateRequestToHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command, $sheetFrom);
    }

    public function testHandleWithStateApprovedForSheetTo()
    {
        // Context
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('email@email.com', 'salt', 'password', 'fr');
        $user3     = new User('email@email.com', 'salt', 'password', 'fr');

        $participant1 = $this->createParticipantMock($sheetTo, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetTo, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetTo, $user3, 3);
        $sheetTo->addParticpant($participant1);
        $sheetTo->addParticpant($participant2);
        $sheetTo->addParticpant($participant3);

        $datetime = new \DateTime('2016-01-24 09:00:00');

        //Actual
        $request = new Request($sheetFrom, [], $sheetTo, [$participant1, $participant2], $datetime, $user1);
        $request->approve($datetime);

        //Command
        $command = new UpdateRequestTo($request, $datetime, $user1);
        $command->participants = [$participant1, $participant3];
        $command->description  = 'modif';

        //Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [0 => $participant1, 2 => $participant3], $datetime, $user1);
        $expectedRequest->approve($datetime);
        $expectedMessage = new Message($expectedRequest, $sheetTo, 'modif', $datetime);

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent($user2, $participant2, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.participant.added',
            new ParticipantAddedEvent($user3, $participant3, $expectedRequest, $expectedMessage->getContent(), $datetime)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.update.message',
            new MessageEvent($expectedMessage, $user1)
        )->shouldBeCalled();

        //Handler
        $handler = new UpdateRequestToHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command, $sheetFrom);
    }

    /**
     * @param Sheet $sheet
     * @param User $user
     * @param $id
     *
     * @return Participant
     */
    public function createParticipantMock(Sheet $sheet, User $user, $id)
    {
        $participant = new Participant($sheet, $user, [], false, true);
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
    }
}
