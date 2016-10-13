<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\UnRefuseMeetingRequest;
use Proximum\Vimeet\Application\Command\Meeting\UnRefuseMeetingRequestHandler;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnRefuseMeetingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3     = new User('test3@test.fr', 'test', 'test', 'fr');
        $dateTime  = new \DateTime();
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);
        $sheetTo   = new Sheet($event, $type, [], $user3, $dateTime);

        // Request to unRefuse
        $request       = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user);
        $unRefuseRequest = new UnRefuseMeetingRequest($request);

        // Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user);
        $expectedRequest->unRefuse($dateTime);

        // Dependencies
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        // Handle

        $handler = new UnRefuseMeetingRequestHandler(
            $requestRepository->reveal(),
            $dateTime
        );

        $handler->handle($unRefuseRequest);
    }
}
