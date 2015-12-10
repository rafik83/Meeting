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
use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Application\Command\Meeting\ApproveRequestHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class ApproveRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event        = new Event();
        $type         = new Type($event);
        $sheetTo      = new Sheet($event, $type, [], []);
        $sheetFrom    = new Sheet($event, $type, [], []);
        $user1        = new User('test@test.fr', 'test', 'test', 'fr');
        $user2        = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3        = new User('test3@test.fr', 'test', 'test', 'fr');
        $user4        = new User('test4@test.fr', 'test', 'test', 'fr');
        $dateTime     = new DateTime;
        $toParticipant3 = $this->createParticipantMock($sheetTo, $user3, 3);
        $toParticipant4 = $this->createParticipantMock($sheetTo, $user4, 4);

        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user1, 1));
        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user2, 2));
        $sheetTo->getParticipants()->add($toParticipant3);
        $sheetTo->getParticipants()->add($toParticipant4);

        $request         = new Request($sheetFrom, [], $sheetTo, 'test', $dateTime);
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, 'test', $dateTime);
        $expectedRequest->addToParticipant($this->createParticipantMock($sheetTo, $user3, 3));
        $expectedRequest->addToParticipant($this->createParticipantMock($sheetTo, $user4, 4));
        $expectedRequest->setState(Request::STATE_APPROVED);

        $approveRequest = new ApproveRequest($request);
        $approveRequest->toParticipants = [$toParticipant3, $toParticipant4];

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $handler = new ApproveRequestHandler($requestRepository->reveal());
        $handler->handle($approveRequest);
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
        $property->setAccessible(false);

        return $participant;
    }
}
