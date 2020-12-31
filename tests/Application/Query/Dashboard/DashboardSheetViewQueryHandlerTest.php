<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetTypeView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DashboardSheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';

        // Expected
        $expectedView = new DashboardSheetView(
            100,
            150, [
                1 =>new DashboardSheetTypeView(1, 70, 'fournisseur'),
                2 => new DashboardSheetTypeView(2, 30, 'investisseur'),
            ], [
                1 => new DashboardSheetTypeView(1, 100, 'fournisseur'),
                2 => new DashboardSheetTypeView(2, 50, 'investisseur'),
            ]
        );

        // Mock
        $sheetRepository       = $this->prophesize(SheetRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $sheetRepository->countEnabledSheetsByEvent($event)->shouldBeCalled()->willReturn(100);
        $participantRepository->countByEnabledSheet($event)->shouldBeCalled()->willReturn(150);

        $sheetRepository->countEnabledSheetsTypeByEvent($event, $locale)->shouldBeCalled()->willReturn([
            ['id' => 1, 'total' => 70, 'title' => 'fournisseur'],
            ['id' => 2, 'total' => 30, 'title' => 'investisseur'],
        ]);

        $participantRepository->countByTypeWithEnabledSheet($event, $locale)->shouldBeCalled()->willReturn([
            ['id' => 1, 'total' => 100, 'title' => 'fournisseur'],
            ['id' => 2, 'total' => 50, 'title' => 'investisseur'],
        ]);

        $handler = new DashboardSheetViewQueryHandler(
            $sheetRepository->reveal(),
            $participantRepository->reveal()
        );

        $view = $handler->handle(new DashboardSheetViewQuery($event, $locale));

        $this->assertEquals($view, $expectedView);
    }
}
