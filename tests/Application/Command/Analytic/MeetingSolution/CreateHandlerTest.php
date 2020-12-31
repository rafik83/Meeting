<?php

namespace Proximum\Vimeet\Tests\Application\Command\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Analytic\MeetingSolution\Create;
use Proximum\Vimeet\Application\Command\Analytic\MeetingSolution\CreateHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\FillingRateQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\FillingRateQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionListQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionListQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate\SpotFillingRateQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate\SpotFillingRateQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Proximum\Vimeet\Domain\Model\Analytic\MeetingSolution;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Analytic\MeetingSolutionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    public $serializer;

    /** @var ObjectProphecy */
    public $meetingSolutionRepository;

    /** @var ObjectProphecy */
    public $meetingRepository;

    /** @var ObjectProphecy */
    public $requestRepository;

    /** @var ObjectProphecy */
    public $spotRepository;

    /** @var ObjectProphecy */
    public $slotRepository;

    /** @var ObjectProphecy */
    public $sheetSatisfactionListQueryHandler;

    /** @var ObjectProphecy */
    public $spotSatisfactionListQueryHandler;

    /** @var ObjectProphecy */
    public $spotFillingRateQueryHandler;

    /** @var ObjectProphecy */
    public $fillingRateQueryHandler;

    /** @var \DateTimeInterface */
    public $dateTime;

    /** @var ObjectProphecy */
    public $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->serializer = $this->prophesize(SerializerAdapterInterface::class);
        $this->meetingSolutionRepository = $this->prophesize(MeetingSolutionRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $this->slotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->sheetSatisfactionListQueryHandler = $this->prophesize(SheetSatisfactionListQueryHandler::class);
        $this->spotSatisfactionListQueryHandler = $this->prophesize(SpotSatisfactionListQueryHandler::class);
        $this->spotFillingRateQueryHandler = $this->prophesize(SpotFillingRateQueryHandler::class);
        $this->fillingRateQueryHandler = $this->prophesize(FillingRateQueryHandler::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandle()
    {
        $spot = $this->prophesize(Spot::class);
        $slot = $this->prophesize(MeetingSlot::class);

        // Mock
        $this->meetingRepository->countByEvent($this->event->reveal())->shouldBeCalled()->willReturn(333);
        $this->requestRepository->countApprovedByEvent($this->event->reveal())->shouldBeCalled()->willReturn(445);
        $this->spotRepository
            ->findSharedByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$spot->reveal()])
        ;
        $this->slotRepository
            ->getAvailableSlotByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$slot->reveal()])
        ;

        $this->meetingSolutionRepository
            ->add(new MeetingSolution(
                $this->event->reveal(),
                333,
                445,
                78,
                'sheetSatisfaction',
                'spotSatisfaction',
                'spotFillingGraph',
                $this->dateTime
            ))->shouldBeCalled();

        $this->fillingRateQueryHandler
            ->handle(new FillingRateQuery($this->event->reveal(), [$slot->reveal()], [$spot->reveal()]))
            ->shouldBeCalled()
            ->willReturn(78)
        ;

        $sheetSatisfactionView = $this->prophesize(SheetSatisfactionView::class);
        $this->sheetSatisfactionListQueryHandler
            ->handle(new SheetSatisfactionListQuery($this->event->reveal()))
            ->shouldBeCalled()
            ->willReturn([$sheetSatisfactionView->reveal()])
        ;

        $spotSatisfactionView = $this->prophesize(SpotSatisfactionView::class);
        $this->spotSatisfactionListQueryHandler
            ->handle(new SpotSatisfactionListQuery($this->event->reveal(), [$slot->reveal()]))
            ->shouldBeCalled()
            ->willReturn([$spotSatisfactionView->reveal()])
        ;

        $spotFillingGraph = $this->prophesize(SpotFillingRateDayView::class);
        $this->spotFillingRateQueryHandler
            ->handle(new SpotFillingRateQuery($this->event->reveal(), [$slot->reveal()], [$spot->reveal()]))
            ->shouldBeCalled()
            ->willReturn([$spotFillingGraph->reveal()]);

        $this->serializer
            ->serialize([$sheetSatisfactionView->reveal()], 'json')
            ->shouldBeCalled()
            ->willReturn('sheetSatisfaction')
        ;
        $this->serializer
            ->serialize([$spotSatisfactionView->reveal()], 'json')
            ->shouldBeCalled()
            ->willReturn('spotSatisfaction')
        ;
        $this->serializer
            ->serialize([$spotFillingGraph->reveal()], 'json')
            ->shouldBeCalled()
            ->willReturn('spotFillingGraph')
        ;

        $handler = new CreateHandler(
            $this->serializer->reveal(),
            $this->meetingSolutionRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->slotRepository->reveal(),
            $this->sheetSatisfactionListQueryHandler->reveal(),
            $this->spotSatisfactionListQueryHandler->reveal(),
            $this->spotFillingRateQueryHandler->reveal(),
            $this->fillingRateQueryHandler->reveal(),
            $this->dateTime
        );
        $handler->handle(new Create($this->event->reveal()));
    }
}
