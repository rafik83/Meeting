<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Sheet\Viewed;

use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ViewedSheetListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $sheetOne = SheetFactory::create();
        $sheetTwo = SheetFactory::create();
        $user     = UserFactory::create();

        $sheetIds = [
            $sheetOne->getId(),
            $sheetTwo->getId(),
        ];

        $sheetViewedRepository = $this->prophesize(SheetViewedRepositoryInterface::class);

        $sheetViewedOne = new SheetViewed($sheetOne, $user, $dateTime);
        $expectedView   = ['' => true];
        $query          = new ViewedSheetListViewQuery($user, $sheetIds);
        $handler        = new ViewedSheetListViewQueryHandler($sheetViewedRepository->reveal());

        $sheetViewedRepository->getSheetsAlreadySeenByUser($user, $sheetIds)
            ->shouldBeCalled()
            ->willReturn([$sheetViewedOne]);

        $resultView = $handler->handle($query);

        $this->assertEquals($expectedView, $resultView);
    }
}
