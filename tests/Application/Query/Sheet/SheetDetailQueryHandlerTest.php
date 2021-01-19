<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQuery;
use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Detail\CRM\RecordViewsQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\CRM\RecordViewsQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Detail\ParticipantDetailQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\ParticipantDetailQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetDetailQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetDetailQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetMeetingIndicatorQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetMeetingIndicatorQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\CRM\RecordView;
use Proximum\Vimeet\Application\View\Sheet\Details\Invoice\InvoiceView;
use Proximum\Vimeet\Application\View\Sheet\Details\OwnerView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetDetailsView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetMeetingIndicatorView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetParticipantsView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\View\OrderVatView;

class SheetDetailQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $recordViewsQueryHandler;

    /** @var ObjectProphecy */
    private $traceRepository;

    /** @var ObjectProphecy */
    private $balance;

    /** @var ObjectProphecy */
    private $invoiceViewQueryHandler;

    /** @var ObjectProphecy */
    private $sheetMeetingIndicatorQueryHandler;

    /** @var ObjectProphecy */
    private $participantDetailQueryHandler;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy|Admin */
    private $admin;

    public function setUp()
    {
        $this->recordViewsQueryHandler = $this->prophesize(RecordViewsQueryHandler::class);
        $this->traceRepository = $this->prophesize(TraceRepositoryInterface::class);
        $this->balance = $this->prophesize(Balance::class);
        $this->invoiceViewQueryHandler = $this->prophesize(InvoiceViewQueryHandler::class);
        $this->sheetMeetingIndicatorQueryHandler = $this->prophesize(SheetMeetingIndicatorQueryHandler::class);
        $this->participantDetailQueryHandler = $this->prophesize(ParticipantDetailQueryHandler::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
    }

    public function testHandle()
    {
        $this->sheet->getTitle()->willReturn('title');
        $this->sheet->getState()->willReturn(Sheet::STATE_PENDING);

        $participantViews = new SheetParticipantsView(
            $this->prophesize(OwnerView::class)->reveal(),
            [$this->prophesize(SheetParticipantsView::class)->reveal()]
        );

        $this->participantDetailQueryHandler
            ->handle(new ParticipantDetailQuery($this->admin->reveal(), $this->sheet->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantViews);

        $meetingIndicatorView = new SheetMeetingIndicatorView(1, 1, 1, 1, 1, 1, 1);
        $this->sheetMeetingIndicatorQueryHandler->handle(new SheetMeetingIndicatorQuery($this->sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn($meetingIndicatorView);

        $recordViews = [$this->prophesize(RecordView::class)->reveal()];
        $this->recordViewsQueryHandler->handle(new RecordViewsQuery($this->sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn($recordViews);

        $this->traceRepository->getAllTracesByObject($this->sheet->reveal())->shouldBeCalled()->willReturn([]);
        $orderVatViews = [
            $this->prophesize(OrderVatView::class)->reveal(),
        ];
        $transactions = [
            $this->prophesize(Transaction::class)->reveal(),
        ];
        $invoiceViews = [
            $this->prophesize(InvoiceView::class)->reveal(),
        ];

        $this->balance->getOrderVatViews($this->sheet->reveal())->shouldBeCalled()->willReturn($orderVatViews);
        $this->balance->getTransactions($this->sheet->reveal())->shouldBeCalled()->willReturn($transactions);
        $this->balance->getTotal($this->sheet->reveal())->shouldBeCalled()->willReturn(123);
        $this->balance->getBalance($this->sheet->reveal())->shouldBeCalled()->willReturn(321);
        $this->sheet->getCompleteness()->willReturn(80);

        $this->invoiceViewQueryHandler
            ->handle(new InvoiceViewQuery($this->sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn($invoiceViews)
        ;

        $template = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createCompanyTemplate($this->sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($template->reveal())
        ;
        $template
            ->getEditableSheetDataExceptedImageObjects()
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $query = new SheetDetailQuery($this->admin->reveal(), $this->sheet->reveal(), 'fr');
        $handler = new SheetDetailQueryHandler(
            $this->recordViewsQueryHandler->reveal(),
            $this->traceRepository->reveal(),
            $this->balance->reveal(),
            $this->invoiceViewQueryHandler->reveal(),
            $this->sheetMeetingIndicatorQueryHandler->reveal(),
            $this->participantDetailQueryHandler->reveal(),
            $this->templateDataFactory->reveal()
        );

        $result = $handler->handle($query);
        $expected = new SheetDetailsView(
            'title',
            Sheet::STATE_PENDING,
            $participantViews,
            $meetingIndicatorView,
            $recordViews,
            [],
            $orderVatViews,
            $transactions,
            $invoiceViews,
            123,
            321,
            80,
            []
        );

        $this->assertEquals($expected, $result);
    }
}
