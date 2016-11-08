<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\UnApproveMeetingRequest;
use Proximum\Vimeet\Application\Command\Meeting\UnApproveMeetingRequestHandler;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUnApproveMeetingRequestException;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnApproveMeetingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3     = new User('test3@test.fr', 'test', 'test', 'fr');
        $dateTime  = new \DateTime();
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);
        $sheetTo   = new Sheet($event, $type, [], $user3, $dateTime);

        // Request to unRefuse
        $request         = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user2);
        $unApproveRequest = new UnApproveMeetingRequest($user3, $request, $sheetTo);

        // Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user2);
        $expectedRequest->unRefuse($dateTime);

        // Dependencies
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();
        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToUnApprove($user3, $request, $sheetTo)->shouldBeCalled()->willReturn(true);

        // Handle
        $handler = new UnApproveMeetingRequestHandler(
            $requestRepository->reveal(),
            $permissionManager->reveal(),
            $dateTime
        );

        $handler->handle($unApproveRequest);
    }

    public function testHandleException()
    {
        $this->expectException(IsNotAllowedToUnApproveMeetingRequestException::class);

        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3     = new User('test3@test.fr', 'test', 'test', 'fr');
        $dateTime  = new \DateTime();
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);
        $sheetTo   = new Sheet($event, $type, [], $user3, $dateTime);

        // Request to unRefuse
        $request         = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user2);
        $unApproveRequest = new UnApproveMeetingRequest($user2, $request, $sheetFrom);

        // Expected
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user2);
        $expectedRequest->unRefuse($dateTime);

        // Dependencies
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldNotBeCalled();
        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToUnApprove($user2, $request, $sheetFrom)->shouldBeCalled()->willReturn(false);

        // Handle
        $handler = new UnApproveMeetingRequestHandler(
            $requestRepository->reveal(),
            $permissionManager->reveal(),
            $dateTime
        );

        $handler->handle($unApproveRequest);
    }
}
