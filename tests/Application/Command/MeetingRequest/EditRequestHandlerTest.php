<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Command\MeetingRequest\EditRequest;
use Proximum\Vimeet\Application\Command\MeetingRequest\EditRequestHandler;
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
    public function testHandle()
    {
        // Context
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $user1     = new User('email@email.com', 'salt', 'password', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3     = new User('test@test.fr', 'test', 'test', 'fr');

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);
        $participant3 = $this->createParticipantMock($sheetFrom, $user3, 3);


        $participantFrom         = [$participant1, $participant2];
        $participantFromExpected = [$participant1, $participant3];

        $datetime = new \DateTime('2016-01-24 09:00:00');

        //Actual
        $request = new Request($sheetFrom, $participantFrom, $sheetTo, [], new \DateTime('2016-01-15 09:00:00'), $user1);

        //Command
        $command = new EditRequest($request, 'modif', $datetime, $user1);
        $command->meetingRequest->removeFromParticipant($participant2);
        $command->meetingRequest->hasFromParticipant($participant3);
        $command->meetingRequest->addFromParticipant($participant3);

        //Expected
        $expectedRequest = new Request($sheetFrom, $participantFromExpected, $sheetTo, [], $datetime, $user1);
        $expectedMessage = new Message($expectedRequest, $sheetFrom, 'this is a test', new \DateTime('2016-01-21 09:00:00'));

        //Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent($user1, $participant2, $expectedRequest, $expectedMessage, new \DateTime('2016-01-24 09:00:00'))
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            'meeting_request.participant.added',
            new ParticipantAddedEvent($user1, $participant3, $expectedRequest, $expectedMessage, new \DateTime('2016-01-24 09:00:00'))
        )->shouldBeCalled();

        //Handler
        $handler = new EditRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
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