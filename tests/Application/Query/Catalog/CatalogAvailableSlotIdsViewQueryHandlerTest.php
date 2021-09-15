<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQueryHandler;
use Proximum\Vimeet\Application\Query\Catalog\CatalogAvailableSlotIdsViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\CatalogAvailableSlotIdsViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Catalog\CatalogAvailableSlotIdsView;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CatalogAvailableSlotIdsViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $availableSlotsByParticipantQueryHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->availableSlotsByParticipantQueryHandler = $this->prophesize(AvailableSlotsByParticipantQueryHandler::class);
    }

    public function testHandleNoFilter()
    {
        $this->sheet->hasUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new CatalogAvailableSlotIdsViewQueryHandler(
            $this->sheetRepository->reveal(),
            $this->availableSlotsByParticipantQueryHandler->reveal()
        );

        $result = $handler->handle(new CatalogAvailableSlotIdsViewQuery(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            []
        ));

        $this->assertEquals(new CatalogAvailableSlotIdsView([], []), $result);
    }

    public function testHandleNoParticipant()
    {
        $this->sheet->hasUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn(false);

        $handler = new CatalogAvailableSlotIdsViewQueryHandler(
            $this->sheetRepository->reveal(),
            $this->availableSlotsByParticipantQueryHandler->reveal()
        );

        $result = $handler->handle(new CatalogAvailableSlotIdsViewQuery(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            [SearchFields::FILTER_AVAILABLE_SLOT_IDS => CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_AVAILABLE]
        ));

        $this->assertEquals(new CatalogAvailableSlotIdsView([], []), $result);
    }

    public function testHandle()
    {
        $otherSheet1 = $this->prophesize(Sheet::class);
        $otherSheet2 = $this->prophesize(Sheet::class);
        $availableSlotView1 = $this->prophesize(AvailableSlotView::class);
        $availableSlotView2 = $this->prophesize(AvailableSlotView::class);
        $availableSlotView3 = $this->prophesize(AvailableSlotView::class);

        $participant = $this->prophesize(Participant::class);
        $this->sheet->hasUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn(true);
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $this->availableSlotsByParticipantQueryHandler
            ->handle(new AvailableSlotsByParticipantQuery($this->event->reveal(), $participant->reveal()))
            ->shouldBeCalled()
            ->willReturn([
                $availableSlotView1->reveal(),
                $availableSlotView2->reveal(),
                $availableSlotView3->reveal(),
            ]);

        $this->sheetRepository
            ->getSheetsWithRequestWithSheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$otherSheet1->reveal(), $otherSheet2->reveal()])
        ;

        $handler = new CatalogAvailableSlotIdsViewQueryHandler(
            $this->sheetRepository->reveal(),
            $this->availableSlotsByParticipantQueryHandler->reveal()
        );

        $result = $handler->handle(new CatalogAvailableSlotIdsViewQuery(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            [SearchFields::FILTER_AVAILABLE_SLOT_IDS => CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_AVAILABLE]
        ));

        $this->assertEquals(
            new CatalogAvailableSlotIdsView(
                [$availableSlotView1->reveal(), $availableSlotView2->reveal(), $availableSlotView3->reveal()],
                [$otherSheet1->reveal(), $otherSheet2->reveal(), $this->sheet->reveal()]
            ),
            $result
        );
    }
}
