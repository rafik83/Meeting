<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequest;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequestHandler;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToCancelMeetingRequestException;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CancelRequestHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $dateTime  = new DateTime();
        $sheetTo   = new Sheet($event, $type, [], $user, $dateTime);
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);

        // Request to cancel
        $request       = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event);
        $cancelRequest = new CancelRequest($request, $user, $sheetFrom);

        // Dependencies
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->remove($request)->shouldBeCalled();
        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToCancel($request, $sheetFrom)->shouldBeCalled()->willReturn(true);

        // Handle

        $handler = new CancelRequestHandler(
            $requestRepository->reveal(),
            $permissionManager->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );
        $handler->handle($cancelRequest);
    }

    public function testHandleException()
    {
        $this->expectException(IsNotAllowedToCancelMeetingRequestException::class);
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $dateTime  = new DateTime();
        $sheetTo   = new Sheet($event, $type, [], $user, $dateTime);
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);

        // Request to cancel
        $request       = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event);
        $cancelRequest = new CancelRequest($request, $user, $sheetFrom);

        // Dependencies
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->remove($request)->shouldNotBeCalled();
        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToCancel($request, $sheetFrom)->shouldBeCalled()->willReturn(false);

        // Handle
        $handler = new CancelRequestHandler(
            $requestRepository->reveal(),
            $permissionManager->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );
        $handler->handle($cancelRequest);
    }

    public function testHandleApproved()
    {
        // Context
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $dateTime  = new DateTime();
        $sheetTo   = new Sheet($event, $type, [], $user, $dateTime);
        $sheetFrom = new Sheet($event, $type, [], $user2, $dateTime);

        // Request to cancel
        $request       = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user, $event);
        $request->approve($dateTime);
        $cancelRequest = new CancelRequest($request, $user, $sheetFrom);

        // Dependencies
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->remove($request)->shouldBeCalled();
        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToCancel($request, $sheetFrom)->shouldBeCalled()->willReturn(true);

        // Handle

        $handler = new CancelRequestHandler(
            $requestRepository->reveal(),
            $permissionManager->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );
        $handler->handle($cancelRequest);
    }
}
