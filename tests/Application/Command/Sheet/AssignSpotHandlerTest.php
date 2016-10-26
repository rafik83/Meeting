<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\AssignSpot;
use Proximum\Vimeet\Application\Command\Sheet\AssignSpotHandler;
use Proximum\Vimeet\Application\Command\Sheet\AssignSpotResult;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotActiveException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AssignSpotHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $type   = new Type($event);
        $spot   = new Spot('A01', $event, 3, 3, 3, true);
        $user   = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test2@test.com', 'salt', 'password', 'fr');
        $date   = new \DateTime();
        $sheet  = new Sheet($event, $type, [], $user, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet2->setSpot($spot);
        $spot->addSheet($sheet2);

        $command = new AssignSpot($event, $sheet, 'A02');

        // Expected
        $spot2  = new Spot('A01', $event, 3, 3, 3, true);
        $expectedSheet = new Sheet($event, $type, [], $user, $date);
        $sheet3 = new Sheet($event, $type, [], $user2, $date);
        $sheet3->setSpot($spot2);
        $spot2->addSheet($sheet3);
        $expectedSheet->setSpot($spot2);
        $spot2->addSheet($expectedSheet);

        // mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->findByReference($event, 'A02')->shouldBeCalled()->willReturn($spot);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        $handler = new AssignSpotHandler($sheetRepository->reveal(), $spotRepository->reveal());
        $result  = $handler->handle($command);

        $expectedResult = new AssignSpotResult(2);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleRemoveAssign()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $spot  = new Spot('A01', $event, 3, 3, 3, true);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $user, $date);

        $command = new AssignSpot($event, $sheet, '');

        $expectedSheet = new Sheet($event, $type, [], $user, $date);
        $expectedSheet->removeSpot();

        // mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($expectedSheet)->shouldBeCalled();
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->findByReference($event, '')->shouldNotBeCalled();

        $handler = new AssignSpotHandler($sheetRepository->reveal(), $spotRepository->reveal());
        $result  = $handler->handle($command);

        $expectedResult = new AssignSpotResult(0);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws SpotNotFoundException
     */
    public function testHandleSpotNotFoundException()
    {
        $this->expectException(SpotNotFoundException::class);

        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $spot  = new Spot('A01', $event, 3, 3, 3, true);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $user, $date);

        $command = new AssignSpot($event, $sheet, 'A02');

        // mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->findByReference($event, 'A02')->shouldBeCalled()->willReturn(null);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($sheet)->shouldNotBeCalled();

        $handler = new AssignSpotHandler($sheetRepository->reveal(), $spotRepository->reveal());
        $handler->handle($command);
    }

    /**
     * @throws SpotNotActiveException
     */
    public function testHandleSpotNotActiveException()
    {
        $this->expectException(SpotNotActiveException::class);

        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $spot  = new Spot('A01', $event, 3, 3, 3, false);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $user, $date);

        $command = new AssignSpot($event, $sheet, 'A02');

        // mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->findByReference($event, 'A02')->shouldBeCalled()->willReturn($spot);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($sheet)->shouldNotBeCalled();

        $handler = new AssignSpotHandler($sheetRepository->reveal(), $spotRepository->reveal());
        $handler->handle($command);
    }
}
