<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidationValidateEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchValidationValidateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $date   = new \DateTime();
        $admin  = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);
        $type   = new Type($event);
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);

        $sheet1->setValidationState(Sheet::STATE_VALIDATION_PENDING);
        $sheet2->setValidationState(Sheet::STATE_VALIDATION_PENDING);
        $sheet3->setValidationState(Sheet::STATE_VALIDATION_PENDING);

        // Expected
        $expectedSheet1 = new Sheet($event, $type, [], $user1, $date);
        $expectedSheet1->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, $date);
        $expectedSheet2->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, $date);
        $expectedSheet3->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);

        $command = new BatchValidationValidate([1, 2, 3], $admin);

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $sheetRepository->getSheetsById([1, 2, 3])
                        ->shouldBeCalled()
                        ->willReturn([$sheet1, $sheet2, $sheet3]);

        foreach ([$expectedSheet1, $expectedSheet2, $expectedSheet3] as $sheet) {
            $sheetRepository->set($sheet)->shouldBeCalled();

            $eventDispatcher->dispatch(Events::SHEET_VALIDATION_VALIDATE,
                new SheetValidationValidateEvent($sheet, $admin, $date)
            )->shouldBeCalled();
        }

        // Handler

        $handler = new BatchValidationValidateHandler(
            $sheetRepository->reveal(),
            $eventDispatcher->reveal(),
            $date
        );

        $result = $handler->handle($command);

        $this->assertEquals(3, $result->count);
    }
}
