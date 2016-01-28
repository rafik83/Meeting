<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Meeting;

use DateTime;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequestHandler;
use Proximum\Vimeet\Application\Event\Meeting\RequestSentEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Context
        $event        = new Event();
        $type         = new Type($event);
        $sheetTo      = new Sheet($event, $type, [], []);
        $sheetFrom    = new Sheet($event, $type, [], []);
        $user1        = new User('test@test.fr', 'test', 'test', 'fr');
        $user2        = new User('test2@test.fr', 'test', 'test', 'fr');

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);

        $sheetFrom->getParticipants()->add($participant1);
        $sheetFrom->getParticipants()->add($participant2);

        $dateTime     = new DateTime;

        // Command
        $createRequest = new CreateRequest($sheetFrom, $sheetTo, $dateTime, $user1);
        $createRequest->description = 'test';
        $createRequest->fromParticipants = [$participant1, $participant2];

        // Expected
        $expectedRequest = new Request($sheetFrom, [$participant1, $participant2], $sheetTo, [], $dateTime, $user1);
        $expectedMessage = new Message($expectedRequest, $sheetFrom, 'test', $dateTime);

        // Deps
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->add($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch('meeting_request.send', new RequestSentEvent($user1, $expectedRequest, $dateTime, 'test'));
        $eventDispatcher->dispatch('meeting_request.participant.added', new ParticipantAddedEvent($user1, $participant1, $expectedRequest, 'test', $dateTime));
        $eventDispatcher->dispatch('meeting_request.participant.added', new ParticipantAddedEvent($user1, $participant2, $expectedRequest, 'test', $dateTime));

        // Handler
        $handler = new CreateRequestHandler($requestRepository->reveal(), $messageRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($createRequest);
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
