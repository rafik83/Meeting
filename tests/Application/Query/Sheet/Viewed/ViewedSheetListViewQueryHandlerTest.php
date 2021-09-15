<?php

namespace Application\Query\Sheet\Viewed;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ViewedSheetListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $sheetOne = SheetFactory::create();
        $sheetTwo = SheetFactory::create();
        $user     = UserFactory::create();

        $sheets = [
            $sheetOne,
            $sheetTwo,
        ];

        $sheetViewedRepository = $this->prophesize(SheetViewedRepositoryInterface::class);

        $sheetViewedOne = new SheetViewed($sheetOne, $user, $dateTime);
        $expectedView   = [123 => true];
        $query          = new ViewedSheetListViewQuery($user, $sheets);
        $handler        = new ViewedSheetListViewQueryHandler($sheetViewedRepository->reveal());

        $sheetViewedRepository->getSheetsAlreadySeenByUser($user, $sheets)
            ->shouldBeCalled()
            ->willReturn([$sheetViewedOne]);

        $resultView = $handler->handle($query);

        $this->assertEquals($expectedView, $resultView);
    }
}
