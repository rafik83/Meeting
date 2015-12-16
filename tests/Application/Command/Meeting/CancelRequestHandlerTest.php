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
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class CancelRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $dateTime  = new DateTime();
        $user      = new User('test@test.fr', 'test', 'test', 'fr');

        $request         = new Request($sheetFrom, [], $sheetTo, [], 'test', $dateTime, $user);
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], 'test', $dateTime, $user);
        $expectedRequest->setState(Request::STATE_CANCEL);

        $cancelRequest = new CancelRequest($request, $user);
        $cancelRequest->message = 'this is a test';

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->add()->shouldNotBeCalled();

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $handler = new CancelRequestHandler(
            $requestRepository->reveal(),
            $notificationRepository->reveal(),
            $dateTime,
            $sheetInfoGuesser->reveal(),
            new NullTranslator()
        );
        $handler->handle($cancelRequest);
    }

    public function testHandleWithNotification()
    {
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $dateTime  = new \DateTimeImmutable();
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $participant = $this->createParticipantMock($sheetTo, $user2, 2);
        $sheetTo->getParticipants()->add($participant);

        // Request to cancel
        $request = new Request(
            $sheetFrom,
            [],
            $sheetTo,
            [$participant],
            'test',
            $dateTime,
            $user
        );

        // Expected request
        $expectedRequest = new Request(
            $sheetFrom,
            [],
            $sheetTo,
            [$participant],
            'test',
            $dateTime,
            $user
        );
        $expectedRequest->setState(Request::STATE_CANCEL);

        // Expected request with notification
        $expectedNotification = new Notification($event, $user, $user2, $dateTime, 'meeting_request.cancel', 'notification.meeting_request.cancel.withoutMessage');
        $expectedRequestWithNotification = new Request(
            $sheetFrom,
            [],
            $sheetTo,
            [$participant],
            'test',
            $dateTime,
            $user
        );
        $expectedRequestWithNotification->setState(Request::STATE_CANCEL);
        $expectedRequestWithNotification->addNotifications($expectedNotification);

        $refusedRequest = new CancelRequest($request, $user, 'this is a test');

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->add($expectedNotification)->shouldBeCalled();

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set(Argument::that(function ($item) use ($expectedRequest, $expectedRequestWithNotification) {
            return $item == $expectedRequest || $item == $expectedRequestWithNotification;
        }))->shouldBeCalledTimes(2);

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $handler = new CancelRequestHandler(
            $requestRepository->reveal(),
            $notificationRepository->reveal(),
            $dateTime,
            $sheetInfoGuesser->reveal(),
            new NullTranslator()
        );
        $handler->handle($refusedRequest);
    }


    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param int   $id
     *
     * @return Participant
     */
    private function createParticipantMock(Sheet $sheet, User $user, $id)
    {
        $participant = new Participant($sheet, $user, [], false);
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);

        return $participant;
    }
}
