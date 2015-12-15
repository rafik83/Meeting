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
use Proximum\Vimeet\Application\Command\Meeting\CancelRequest;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequestHandler;
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

        $refusedRequest = new CancelRequest($request, $user);
        $refusedRequest->message = 'this is a test';

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->add()->shouldNotBeCalled();

        $handler = new CancelRequestHandler($requestRepository->reveal(), $notificationRepository->reveal(), $dateTime);
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

        $sheetTo->getParticipants()->add($this->createParticipantMock($sheetFrom, $user2, 2));

        $expectedNotification = new Notification($user, $user2, $dateTime, 'meeting_request.cancel');
        $expectedNotification->setMessage('this is a test');

        $request = new Request(
            $sheetFrom,
            [],
            $sheetTo,
            [$this->createParticipantMock($sheetFrom, $user2, 2)],
            'test',
            $dateTime,
            $user
        );

        $expectedRequest = new Request(
            $sheetFrom,
            [],
            $sheetTo,
            [$this->createParticipantMock($sheetFrom, $user2, 2)],
            'test',
            $dateTime,
            $user
        );
        $expectedRequest->setState(Request::STATE_CANCEL);
        $expectedRequest->addNotifications($expectedNotification);

        $refusedRequest = new CancelRequest($request, $user);
        $refusedRequest->message = 'this is a test';


        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->add($expectedNotification)->shouldBeCalled();

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $handler = new CancelRequestHandler($requestRepository->reveal(), $notificationRepository->reveal(), $dateTime);
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
        $participant = new Participant($sheet, $user, [], false);
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);

        return $participant;
    }
}
