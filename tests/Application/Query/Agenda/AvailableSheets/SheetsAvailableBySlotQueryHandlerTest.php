<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\AvailableSheets;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQueryHandler;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableBySlotQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $visibleParticipationTypes;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->visibleParticipationTypes = $this->prophesize(VisibleParticipationTypes::class);
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $userSheet = $this->prophesize(Sheet::class);
        $userSheet->getId()->willReturn(1);

        $otherSheet = $this->prophesize(Sheet::class);
        $otherSheet->getId()->willReturn(2);

        $slot = $this->prophesize(MeetingSlot::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $allowedTypes = [
            $type1->reveal(),
            $type2->reveal(),
        ];

        $this->sheetRepository->getSheetsWithRequestWithSheet($userSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$otherSheet->reveal()])
        ;

        $this->sheetRepository
            ->countAvailableSheetsInCatalogWithTypesByEvent(
                $event->reveal(),
                $allowedTypes,
                $slot->reveal(),
                [
                    1 => $userSheet->reveal(),
                    2 => $otherSheet->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(42)
        ;

        $this->visibleParticipationTypes
            ->getAllowedTypesList($userSheet->reveal())
            ->shouldBeCalled()
            ->willReturn($allowedTypes)
        ;

        $query = new SheetsAvailableBySlotQuery(
            $event->reveal(),
            $userSheet->reveal(),
            $slot->reveal()
        );

        $handler = new SheetsAvailableBySlotQueryHandler(
            $this->sheetRepository->reveal(),
            $this->visibleParticipationTypes->reveal()
        );

        $result = $handler->handle($query);

        $this->assertEquals(42, $result);
    }
}
