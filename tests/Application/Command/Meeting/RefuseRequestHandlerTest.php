<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use DateTime;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequestHandler;
use Proximum\Vimeet\Application\Event\MeetingRequest\RefusedRequestEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class RefuseRequestHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3     = new User('test3@test.fr', 'test', 'test', 'fr');
        $dateTime  = new DateTime();
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);
        $sheetTo   = new Sheet($event, $type, [], $user3, $dateTime);

        // Request to refuse
        $request       = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event);
        $refuseRequest = new RefuseRequest($request, $user, $dateTime);
        $refuseRequest->message = 'this is a test';

        // Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event, false, true);
        $expectedRequest->refuse($dateTime);
        $expectedMessage = new Message($request, $sheetTo, 'this is a test', $dateTime);
        $exectedEvent    = new RefusedRequestEvent($user, $request, $dateTime, 'this is a test');

        // Dependencies
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher->dispatch('meeting_request.refused', $exectedEvent);

        // Handle

        $handler = new RefuseRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );

        $handler->handle($refuseRequest);
    }

    public function testHandleWithoutMessage()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3     = new User('test3@test.fr', 'test', 'test', 'fr');
        $dateTime  = new DateTime();
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);
        $sheetTo   = new Sheet($event, $type, [], $user3, $dateTime);

        // Request to refuse
        $request       = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event);
        $refuseRequest = new RefuseRequest($request, $user, $dateTime);

        // Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event, false, false);
        $expectedRequest->refuse($dateTime);
        $expectedMessage = new Message($request, $sheetTo, 'this is a test', $dateTime);
        $exectedEvent    = new RefusedRequestEvent($user, $request, $dateTime, 'this is a test');

        // Dependencies
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldNotBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher->dispatch('meeting_request.refused', $exectedEvent);

        // Handle

        $handler = new RefuseRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );

        $handler->handle($refuseRequest);
    }
}
