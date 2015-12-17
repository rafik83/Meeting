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
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequestHandler;
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

class RefuseRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $dateTime  = new DateTime();
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');

        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user, 2));

        // Request to refuse
        $request         = new Request($sheetFrom, [], $sheetTo, [], 'test', $dateTime, $user);

        // Expected request
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, [], 'test', $dateTime, $user);
        $expectedRequest->setState(Request::STATE_REFUSED);

        // Expected request with notification
        $expectedNotification = new Notification($event, $user2, $user, $dateTime, 'meeting_request.refuse', 'notification.meeting_request.refuse.withMessage');
        $expectedRequestWithNotification = new Request($sheetFrom, [], $sheetTo, [], 'test', $dateTime, $user);
        $expectedRequestWithNotification->setState(Request::STATE_REFUSED);
        $expectedRequestWithNotification->addNotifications($expectedNotification);

        // Command
        $refusedRequest = new RefuseRequest($request, $user2);
        $refusedRequest->message = 'this is a test';

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set(Argument::that(function ($item) use ($expectedRequest, $expectedRequestWithNotification) {
            return $item == $expectedRequest || $item == $expectedRequestWithNotification;
        }))->shouldBeCalledTimes(2);

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->add($expectedNotification)->shouldBeCalled();

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $handler = new RefuseRequestHandler(
            $requestRepository->reveal(),
            $notificationRepository->reveal(),
            $dateTime,
            $sheetInfoGuesser->reveal(),
            new NullTranslator()
        );
        $handler->handle($refusedRequest);
    }

    public function testHandleWithNotification()
    {
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $dateTime  = new DateTime();
        $user      = new User('test@test.fr', 'test', 'test', 'fr');
        $user2     = new User('test2@test.fr', 'test', 'test', 'fr');
        $participant = $this->createParticipantMock($sheetFrom, $user2, 2);

        $sheetFrom->getParticipants()->add($participant);

        // Request to refuse
        $request = new Request(
            $sheetFrom,
            [$participant],
            $sheetTo,
            [],
            'test',
            $dateTime,
            $user
        );

        // Expected request
        $expectedRequest = new Request(
            $sheetFrom,
            [$participant],
            $sheetTo,
            [],
            'test',
            $dateTime,
            $user
        );
        $expectedRequest->setState(Request::STATE_REFUSED);

        // Expected request with notification
        $expectedNotification = new Notification($event, $user, $user2, $dateTime, 'meeting_request.refuse', 'notification.meeting_request.refuse.withMessage');
        $expectedRequestWithNotification = new Request(
            $sheetFrom,
            [$participant],
            $sheetTo,
            [],
            'test',
            $dateTime,
            $user
        );
        $expectedRequestWithNotification->setState(Request::STATE_REFUSED);
        $expectedRequestWithNotification->addNotifications($expectedNotification);

        // Command
        $refusedRequest = new RefuseRequest($request, $user);
        $refusedRequest->message = 'this is a test';

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->add($expectedNotification)->shouldBeCalled();

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set(Argument::that(function ($item) use ($expectedRequest, $expectedRequestWithNotification) {
            return $item == $expectedRequest || $item == $expectedRequestWithNotification;
        }))->shouldBeCalledTimes(2);

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $handler = new RefuseRequestHandler(
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
     * @param User $user
     * @param $id
     *
     * @return Participant
     */
    public function createParticipantMock(Sheet $sheet, User $user, $id)
    {
        $participant = new Participant($sheet, $user, [], true);
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);

        return $participant;
    }
}
