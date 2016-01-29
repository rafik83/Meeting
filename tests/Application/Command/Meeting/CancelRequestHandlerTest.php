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
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequest;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequestHandler;
use Proximum\Vimeet\Application\Event\Meeting\RequestCanceledEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CancelRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Context

        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $dateTime  = new DateTime();
        $user      = new User('test@test.fr', 'test', 'test', 'fr');

        // Request to cancel

        $request = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user);
        $cancelRequest = new CancelRequest($request, $user, $dateTime);
        $cancelRequest->message = 'this is a test';

        // Expceted

        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user);
        $expectedRequest->cancel($dateTime);
        $expectedMessage = new Message($expectedRequest, $sheetFrom, 'this is a test', $dateTime);
        $exectedEvent    = new RequestCanceledEvent($user, $request, $dateTime, 'this is a test');

        // Dependencies

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($expectedMessage)->shouldBeCalled();


        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch('meeting_request.canceled', $exectedEvent);

        // Handle

        $handler = new CancelRequestHandler(
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );
        $handler->handle($cancelRequest);
    }
}
