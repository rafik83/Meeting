<?php

namespace Application\Query\Agenda\Admin\Indicator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator\SheetIndicatorsViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator\SheetIndicatorsViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\Indicator\SheetIndicatorsView;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Planner\IndicatorView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SheetIndicatorsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $meetingRepository   = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepository   = $this->prophesize(RequestRepositoryInterface::class);
        $indicatorCalculator = $this->prophesize(IndicatorCalculator::class);

        $dateTime = new \DateTime();
        $user  = UserFactory::create('awsm@elao.com');
        $event = EventFactory::createEvent('Awsm Title');
        $sheet = SheetFactory::create($event, $user, $dateTime);
        $request = 1;
        $propositions = 1;

        $indicators = new IndicatorView(1, 1, 0, 1, 1, 1, 0, 8, null);

        $query = new SheetIndicatorsViewQuery($sheet);
        $handler = new SheetIndicatorsViewQueryHandler(
            $meetingRepository->reveal(),
            $requestRepository->reveal(),
            $indicatorCalculator->reveal()
        );
        $expectedView = new SheetIndicatorsView(1, 1, 1, 1, 1, 1, 1);

        $requestRepository->countRequestSentBySheet($sheet)->shouldBeCalled()->willReturn($request);
        $requestRepository->countPropositionReceivedBySheet($sheet)->shouldBeCalled()->willReturn($propositions);

        $indicatorCalculator->getIndicator($sheet)->shouldBeCalled()->willReturn($indicators);
        $meetingRepository->countMeetingsOfSheet($sheet)->shouldBeCalled()->willReturn(1);

        $resultView = $handler->handle($query);

        $this->assertEquals($expectedView, $resultView);
    }
}
