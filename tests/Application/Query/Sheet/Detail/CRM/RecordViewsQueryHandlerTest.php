<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Detail\CRM;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Sheet\Detail\CRM\RecordViewsQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\CRM\RecordViewsQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\CRM\RecordView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;

class RecordViewsQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $traceRepository;

    /** @var ObjectProphecy */
    private $commentRepository;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $admin;

    public function setUp()
    {
        $this->traceRepository = $this->prophesize(TraceRepositoryInterface::class);
        $this->commentRepository = $this->prophesize(CommentRepositoryInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getTraceableName()->willReturn('sheet');
        $this->sheet->getId()->willReturn(12);
        $this->admin = $this->prophesize(Admin::class);
    }

    public function testHandle()
    {
        $date2 = new \DateTime('2017-10-10 10:10:10.000');
        $date5 = new \DateTime('2017-10-11 10:10:10.000');
        $date4 = new \DateTime('2017-11-09 10:10:10.000');
        $date3 = new \DateTime('2017-11-10 10:10:10.000');
        $date1 = new \DateTime('2017-12-10 10:10:10.000');
        $date6 = new \DateTime('2017-12-12 10:10:10.000');

        $comments = [
            new Sheet\Comment(
                $this->sheet->reveal(),
                $this->admin->reveal(),
                'text 1',
                $date1
            ),
            new Sheet\Comment(
                $this->sheet->reveal(),
                $this->admin->reveal(),
                'text 2',
                $date2
            ),
            new Sheet\Comment(
                $this->sheet->reveal(),
                $this->admin->reveal(),
                'text 3',
                $date3
            ),
        ];

        $traces = [
            new Trace(
                $this->sheet->reveal(),
                Trace::SET_COMMERCIAL_STATUS,
                $date4,
                CommercialStatus::STATUS_DO_NOT_CALL,
                $this->admin->reveal()
            ),
            new Trace(
                $this->sheet->reveal(),
                Trace::SET_COMMERCIAL_STATUS,
                $date5,
                CommercialStatus::STATUS_HOT_STALL,
                $this->admin->reveal()
            ),
            new Trace(
                $this->sheet->reveal(),
                Trace::SET_COMMERCIAL_STATUS,
                $date6,
                CommercialStatus::STATUS_INTEREST,
                $this->admin->reveal()
            ),
        ];

        $this->commentRepository
            ->getCommentsBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($comments)
        ;
        $this->traceRepository
            ->getAllTracesByObjectAndAction($this->sheet->reveal(), Trace::SET_COMMERCIAL_STATUS)
            ->shouldBeCalled()
            ->willReturn($traces)
        ;
        $query = new RecordViewsQuery($this->sheet->reveal());
        $handler = new RecordViewsQueryHandler(
            $this->traceRepository->reveal(),
            $this->commentRepository->reveal()
        );

        $result = $handler->handle($query);

        $expected = [
            new RecordView($this->admin->reveal(), CommercialStatus::STATUS_INTEREST, RecordView::TRACE, $date6),
            new RecordView($this->admin->reveal(), 'text 1', RecordView::COMMENT, $date1),
            new RecordView($this->admin->reveal(), 'text 3', RecordView::COMMENT, $date3),
            new RecordView($this->admin->reveal(), CommercialStatus::STATUS_DO_NOT_CALL, RecordView::TRACE, $date4),
            new RecordView($this->admin->reveal(), CommercialStatus::STATUS_HOT_STALL, RecordView::TRACE, $date5),
            new RecordView($this->admin->reveal(), 'text 2', RecordView::COMMENT, $date2),
        ];

        $this->assertEquals($expected, $result);
    }
}
