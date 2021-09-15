<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
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

class AssignSpotHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $spot  = new Spot('A02', $event, 3, 3, 3, true, 8);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2 = new User('test2@test.com', 'salt', 'password', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $user, $date);
        $sheet->setSpot($spot);
        $spot->addSheet($sheet);

        $command = new AssignSpot($event, $sheet, 'A03');

        // Expected
        $spot2  = new Spot('A03', $event, 3, 3, 3, true, 12);
        $sheet3 = new Sheet($event, $type, [], $user2, $date);
        $sheet3->setSpot($spot2);
        $spot2->addSheet($sheet3);

        // Expected spot with new priority
        $spotP  = new Spot('A03', $event, 3, 3, 3, true, 8);
        $sheet3P = new Sheet($event, $type, [], $user2, $date);
        $sheet3P->setSpot($spotP);
        $spotP->addSheet($sheet3P);

        // Expected old spot
        $expectedOldSpot = new Spot('A02', $event, 3, 3, 3, true, 12);

        // Expected sheet final
        $expectedSheet = new Sheet($event, $type, [], $user, $date);
        // With expected spot
        $spotPE   = new Spot('A03', $event, 3, 3, 3, true, 8);
        $sheet3PE = new Sheet($event, $type, [], $user2, $date);
        $sheet3PE->setSpot($spotPE);
        $spotPE->addSheet($sheet3PE);
        $expectedSheet->setSpot($spotPE);
        $spotPE->addSheet($expectedSheet);

        // Reflection
        $reflection      = new \ReflectionClass(Spot::class);
        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $property      = $reflection->getProperty('id');
        $propertySheet = $reflectionSheet->getProperty('id');
        $property->setAccessible(true);
        $propertySheet->setAccessible(true);
        $property->setValue($spot, 1);
        $property->setValue($spot2, 2);
        $property->setValue($spotP, 2);
        $property->setValue($spotPE, 2);
        $property->setValue($expectedOldSpot, 1);
        $propertySheet->setValue($sheet, 1);
        $propertySheet->setValue($sheet3, 2);
        $propertySheet->setValue($sheet3P, 2);
        $propertySheet->setValue($sheet3PE, 2);
        $propertySheet->setValue($expectedSheet, 1);
        $property->setAccessible(false);

        // mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->findByReference($event, 'A03')->shouldBeCalled()->willReturn($spot2);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $spotRepository->set($spotPE)->shouldBeCalled();
        $sheetRepository->set($expectedSheet)->shouldNotBeCalled();
        $spotRepository->set($expectedOldSpot)->shouldBeCalled();

        $handler = new AssignSpotHandler($sheetRepository->reveal(), $spotRepository->reveal());
        $result  = $handler->handle($command);

        $expectedResult = new AssignSpotResult(2);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleRemoveAssign()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $spot  = new Spot('A01', $event, 3, 3, 3, true, 8);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $user, $date);
        $sheet->setSpot($spot);

        $command = new AssignSpot($event, $sheet, '');

        $expectedSheet = new Sheet($event, $type, [], $user, $date);
        $expectedSheet->removeSpot();

        $expectedSpot = new Spot('A01', $event, 3, 3, 3, true, 12);

        // mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($expectedSheet)->shouldBeCalled();
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->findByReference($event, '')->shouldNotBeCalled();
        $spotRepository->set($expectedSpot)->shouldBeCalled();

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
