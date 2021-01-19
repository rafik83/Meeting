<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\MeetingSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\MeetingSlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\SpotMeetingSlotView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;

class MeetingSlotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale    = 'fr';
        $fromSheet = $this->prophesize(Sheet::class);
        $toSheet   = $this->prophesize(Sheet::class);

        $meeting   = MeetingFactory::createMeeting($fromSheet->reveal(), $toSheet->reveal());

        // Mock
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);
        $fromSheet->getId()->willReturn(1);
        $toSheet->getId()->willReturn(2);

        $sheetInfoGuesser
            ->guessSheetTitle($fromSheet->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('Elao');

        $sheetInfoGuesser
            ->guessSheetTitle($toSheet->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('Proximum');

        // Expected
        $expectedSpotMeetingSlotView = new SpotMeetingSlotView(
            1,
            'Elao',
            2,
            'Proximum'
        );

        $query   = new MeetingSlotViewQuery($meeting, $locale);
        $handler = new MeetingSlotViewQueryHandler($sheetInfoGuesser->reveal());

        $spotMeetingSlotView = $handler->handle($query);

        $this->assertEquals($expectedSpotMeetingSlotView, $spotMeetingSlotView);
    }
}
