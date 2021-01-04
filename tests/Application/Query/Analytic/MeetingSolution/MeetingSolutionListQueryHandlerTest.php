<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\MeetingSolutionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\MeetingSolutionListQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\MeetingSolutionView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionListView;
use Proximum\Vimeet\Domain\Model\Analytic\MeetingSolution;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Analytic\MeetingSolutionRepositoryInterface;

class MeetingSolutionListQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);
        $meetingSolution1 = $this->prophesize(MeetingSolution::class);
        $meetingSolution2 = $this->prophesize(MeetingSolution::class);
        $meetingSolutions = [$meetingSolution1->reveal(), $meetingSolution2->reveal()];

        $meetingSolution1->getCreatedAt()->willReturn($dateTime);
        $meetingSolution2->getCreatedAt()->willReturn($dateTime);
        $meetingSolution1->getSheetSatisfaction()->willReturn('sheetSatisfaction1');
        $meetingSolution1->getSpotSatisfaction()->willReturn('spotSatisfaction1');
        $meetingSolution1->getSpotFillingGraph()->willReturn('spotFillingRate1');
        $meetingSolution2->getSheetSatisfaction()->willReturn('sheetSatisfaction2');
        $meetingSolution2->getSpotSatisfaction()->willReturn('spotSatisfaction2');
        $meetingSolution2->getSpotFillingGraph()->willReturn('spotFillingRate2');
        $meetingSolution1->getMeetings()->willReturn(67);
        $meetingSolution1->getRequests()->willReturn(78);
        $meetingSolution1->getFillingRate()->willReturn(90);
        $meetingSolution2->getMeetings()->willReturn(111);
        $meetingSolution2->getRequests()->willReturn(167);
        $meetingSolution2->getFillingRate()->willReturn(56);

        $sheetSatisfactionListView1 = $this->prophesize(SheetSatisfactionListView::class);
        $sheetSatisfactionListView2 = $this->prophesize(SheetSatisfactionListView::class);

        $spotSatisfactionListView1 = $this->prophesize(SpotSatisfactionListView::class);
        $spotSatisfactionListView2 = $this->prophesize(SpotSatisfactionListView::class);

        $spotFillingRateDayListView1 = $this->prophesize(SpotFillingRateDayListView::class);
        $spotFillingRateDayListView2 = $this->prophesize(SpotFillingRateDayListView::class);

        // Mock
        $meetingSolutionRepository = $this->prophesize(MeetingSolutionRepositoryInterface::class);
        $meetingSolutionRepository->findByEvent($event->reveal())->shouldBeCalled()->willReturn($meetingSolutions);
        $serializer = $this->prophesize(SerializerAdapterInterface::class);

        $serializer
            ->deserialize('sheetSatisfaction1', SheetSatisfactionListView::class, 'json')
            ->shouldBeCalled()
            ->willReturn($sheetSatisfactionListView1->reveal())
        ;

        $serializer
            ->deserialize('sheetSatisfaction2', SheetSatisfactionListView::class, 'json')
            ->shouldBeCalled()
            ->willReturn($sheetSatisfactionListView2->reveal())
        ;

        $serializer
            ->deserialize('spotSatisfaction1', SpotSatisfactionListView::class, 'json')
            ->shouldBeCalled()
            ->willReturn($spotSatisfactionListView1->reveal())
        ;

        $serializer
            ->deserialize('spotSatisfaction2', SpotSatisfactionListView::class, 'json')
            ->shouldBeCalled()
            ->willReturn($spotSatisfactionListView2->reveal())
        ;

        $serializer
            ->deserialize('spotFillingRate1', SpotFillingRateDayListView::class, 'json')
            ->shouldBeCalled()
            ->willReturn($spotFillingRateDayListView1->reveal())
        ;

        $serializer
            ->deserialize('spotFillingRate2', SpotFillingRateDayListView::class, 'json')
            ->shouldBeCalled()
            ->willReturn($spotFillingRateDayListView2->reveal())
        ;

        $handler = new MeetingSolutionListQueryHandler($meetingSolutionRepository->reveal(), $serializer->reveal());
        $result = $handler->handle(new MeetingSolutionListQuery($event->reveal()));

        $expected = [
            new MeetingSolutionView(
                67,
                78,
                90,
                $sheetSatisfactionListView1->reveal(),
                $spotSatisfactionListView1->reveal(),
                $spotFillingRateDayListView1->reveal(),
                $dateTime
            ),
            new MeetingSolutionView(
                111,
                167,
                56,
                $sheetSatisfactionListView2->reveal(),
                $spotSatisfactionListView2->reveal(),
                $spotFillingRateDayListView2->reveal(),
                $dateTime
            ),
        ];

        $this->assertEquals($expected, $result);
    }
}
