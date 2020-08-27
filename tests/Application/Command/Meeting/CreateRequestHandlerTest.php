<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequestHandler;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequestResult;
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

class CreateRequestHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event        = EventFactory::createEvent();
        $type         = new Type($event);
        $user1        = new User('test@test.fr', 'test', 'test', 'fr');
        $user2        = new User('test2@test.fr', 'test', 'test', 'fr');
        $sheetTo      = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheetFrom    = new Sheet($event, $type, [], $user2, new \DateTime());

        $participant1 = $this->createParticipantMock($sheetFrom, $user1, 1);
        $participant2 = $this->createParticipantMock($sheetFrom, $user2, 2);

        $sheetFrom->getParticipants()->add($participant1);
        $sheetFrom->getParticipants()->add($participant2);

        $dateTime     = new DateTime();

        // Command
        $createRequest = new CreateRequest($event, $sheetFrom, $sheetTo, $user1, "fr");
        $createRequest->description = 'test';
        $createRequest->participants = [$participant1, $participant2];

        // Expected
        $expectedRequest = new Request($sheetFrom, [$participant1, $participant2], $sheetTo, [], $dateTime, $user1, $event, false, true);
        $expectedMessage = new Message($expectedRequest, $sheetFrom, 'test', $dateTime);

        // Deps
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->add($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Handler
        $handler = new CreateRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );

        $result = $handler->handle($createRequest);

        $meetingRequestResult = new CreateRequestResult($expectedRequest);
        $this->assertEquals($meetingRequestResult, $result);
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
