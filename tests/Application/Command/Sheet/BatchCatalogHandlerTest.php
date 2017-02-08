<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchCatalogHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN',
            new \DateTime());

        // actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $sheet3 = new Sheet($event, $type, [], $user3, new \DateTime());

        // expected sheet
        $expectedSheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $expectedSheet1->setInCatalog(true);
        $expectedSheet1->setInCatalogAt($date);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $expectedSheet2->setInCatalog(true);
        $expectedSheet2->setInCatalogAt($date);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, new \DateTime());
        $expectedSheet3->setInCatalog(true);
        $expectedSheet3->setInCatalogAt($date);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet1, 1);
        $property->setValue($sheet2, 2);
        $property->setValue($sheet3, 3);
        $property->setValue($expectedSheet1, 1);
        $property->setValue($expectedSheet2, 2);
        $property->setValue($expectedSheet3, 3);
        $property->setAccessible(false);

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);
        $meetingRepository->countMeetingsOfSheet($sheet1)->shouldNotBeCalled();
        $meetingRepository->countMeetingsOfSheet($sheet2)->shouldNotBeCalled();
        $meetingRepository->countMeetingsOfSheet($sheet3)->shouldNotBeCalled();
        $enableDisableManager->update($sheet1, true)->shouldBeCalled();
        $enableDisableManager->update($sheet2, true)->shouldBeCalled();
        $enableDisableManager->update($sheet3, true)->shouldBeCalled();
        $sheetRepository->set($expectedSheet1)->shouldBeCalled();
        $sheetRepository->set($expectedSheet2)->shouldBeCalled();
        $sheetRepository->set($expectedSheet3)->shouldBeCalled();

        // Command
        $command = new BatchCatalog([1, 2, 3], true, $admin);
        $handler = new BatchCatalogHandler(
            $sheetRepository->reveal(),
            $eventDispatcher->reveal(),
            $date,
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $enableDisableManager->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }

    public function testHandleWithIgnoredSheets()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();
        $dateold = new \DateTime('2016-10-12 10:10');

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN',
            new \DateTime());

        // actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheet1->setInCatalog(true);
        $sheet1->setInCatalogAt($dateold);
        $sheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $sheet2->setInCatalog(true);
        $sheet2->setInCatalogAt($dateold);
        $sheet3 = new Sheet($event, $type, [], $user3, new \DateTime());
        $sheet3->setInCatalog(true);
        $sheet3->setInCatalogAt($dateold);

        // expected sheet
        $expectedSheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $expectedSheet1->setInCatalog(false);
        $expectedSheet1->setInCatalogAt($date);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $expectedSheet2->setInCatalog(false);
        $expectedSheet2->setInCatalogAt($dateold);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, new \DateTime());
        $expectedSheet3->setInCatalog(true);
        $expectedSheet3->setInCatalogAt($date);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet1, 1);
        $property->setValue($sheet2, 2);
        $property->setValue($sheet3, 3);
        $property->setValue($expectedSheet1, 1);
        $property->setValue($expectedSheet2, 2);
        $property->setValue($expectedSheet3, 3);
        $property->setAccessible(false);

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);
        $sheetRepository->set($sheet1)->shouldBeCalled();
        $sheetRepository->set($sheet3)->shouldBeCalled();
        $meetingRepository->countMeetingsOfSheet($sheet1)->shouldBeCalled()->willReturn(0);
        $meetingRepository->countMeetingsOfSheet($sheet2)->shouldBeCalled()->willReturn(2);
        $meetingRepository->countMeetingsOfSheet($sheet3)->shouldBeCalled()->willReturn(0);
        $sheetInfoGuesser->guessSheetTitle($sheet2, 'fr')->shouldBeCalled()->willReturn("SheetName");
        $enableDisableManager->update($sheet1, false)->shouldBeCalled();
        $enableDisableManager->update($sheet3, false)->shouldBeCalled();

        // Command
        $command = new BatchCatalog([1, 2, 3], false, $admin);
        $handler = new BatchCatalogHandler(
            $sheetRepository->reveal(),
            $eventDispatcher->reveal(),
            $date,
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $enableDisableManager->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
        $this->assertEquals("SheetName", $result->ignoredSheetsMessage);
    }
}
