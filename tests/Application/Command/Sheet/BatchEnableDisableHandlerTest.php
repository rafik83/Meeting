<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation\EnableDisableManager;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchEnableDisableHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);

        // Actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);

        // Expected
        $expectedSheet1 = new Sheet($event, $type, [], $user1, $date);
        $expectedSheet1->setEnable(false);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, $date);
        $expectedSheet2->setEnable(false);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, $date);
        $expectedSheet3->setEnable(false);

        // Mock
        $sheetRepository      = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher      = $this->prophesize(DelayedEventDispatcher::class);
        $batchCatalogHandler  = $this->prophesize(BatchCatalogHandler::class);
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);
        $meetingRepository    = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser     = $this->prophesize(SheetInfoGuesser::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1->setEnable(false), $sheet2->setEnable(false), $sheet3->setEnable(false)]);
        $meetingRepository->countMeetingsOfSheet($sheet3)->shouldBeCalled()->willReturn(0);


        foreach ([$expectedSheet1, $expectedSheet2, $expectedSheet3] as $expectedSheet) {
            $meetingRepository->countMeetingsOfSheet($expectedSheet)->shouldBeCalled()->willReturn(0);
            $sheetRepository->set($expectedSheet)->shouldBeCalled();
            $eventDispatcher->dispatch(Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent($expectedSheet, $admin, $date, false)
            )->shouldBeCalled();
            $enableDisableManager->update($expectedSheet, false)->shouldBeCalled();
        }

        // Command
        $command = new BatchEnableDisable([1, 2, 3], false, $admin);
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $eventDispatcher->reveal(),
            $batchCatalogHandler->reveal(),
            $date,
            $enableDisableManager->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }

    public function testHandleWithIgnoredSheets()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);

        // Actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);

        // Expected
        $expectedSheet1 = new Sheet($event, $type, [], $user1, $date);
        $expectedSheet1->setEnable(false);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, $date);
        $expectedSheet2->setEnable(false);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, $date);
        $expectedSheet3->setEnable(false);

        // Mock
        $sheetRepository      = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher      = $this->prophesize(DelayedEventDispatcher::class);
        $batchCatalogHandler  = $this->prophesize(BatchCatalogHandler::class);
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);
        $meetingRepository    = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser     = $this->prophesize(SheetInfoGuesser::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);
        $meetingRepository->countMeetingsOfSheet($sheet3)->willReturn(2);
        $sheetInfoGuesser->guessSheetTitle($sheet3, 'fr')->shouldBeCalled()->willReturn("SheetName");

        // Command
        $command = new BatchEnableDisable([1, 2, 3], false, $admin);
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $eventDispatcher->reveal(),
            $batchCatalogHandler->reveal(),
            $date,
            $enableDisableManager->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
        $this->assertEquals("SheetName, SheetName, SheetName", $result->ignoredSheetsMessage);
    }
}
