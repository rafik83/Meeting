<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Group\Sheet;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Sheet\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Group\Sheet\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

class SheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheetRepository  = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);

        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent('title');
        $user     = UserFactory::create('test@elao.com');
        $sheet    = SheetFactory::create($event, $user, $dateTime);

        $sheetViewQuery = new SheetViewQuery($event, $user, 'fr');
        $expectedView   = new SheetView(null, null);
        $handler        = new SheetViewQueryHandler($sheetRepository->reveal(), $sheetInfoGuesser->reveal());

        $sheetRepository->getAllSheetsByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([$sheet]);

        $view = $handler->handle($sheetViewQuery);

        $this->assertEquals([$expectedView], $view);
    }
}
