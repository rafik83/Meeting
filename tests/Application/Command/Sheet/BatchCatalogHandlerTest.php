<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchCatalogHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();

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

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        foreach ([$expectedSheet1, $expectedSheet2, $expectedSheet3] as $sheet) {
            $sheetRepository->set($sheet)->shouldBeCalled();
        }

        // Command
        $command = new BatchCatalog([1, 2, 3], $date, true);
        $handler = new BatchCatalogHandler($sheetRepository->reveal());

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }
}
