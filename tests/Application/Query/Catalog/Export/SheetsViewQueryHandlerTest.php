<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog\Export;

use Elastica\Result;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Catalog\GetViewedSheetsFromFilters;
use Proximum\Vimeet\Application\Components\Sheet\Nomenclature\NomenclatureItemsGetter;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetsViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetsViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetListView;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Sheet\SheetIdView;

class SheetsViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $sheetSearchAdapter;

    /** @var ObjectProphecy */
    private $nomenclatureItemsGetter;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $sheetViewQueryHandler;

    /** @var ObjectProphecy */
    private $getViewedSheetsFromFilters;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    public function setUp()
    {
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);
        $this->nomenclatureItemsGetter = $this->prophesize(NomenclatureItemsGetter::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->sheetViewQueryHandler = $this->prophesize(SheetViewQueryHandler::class);
        $this->getViewedSheetsFromFilters = $this->prophesize(GetViewedSheetsFromFilters::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->event->getLocaleFallback()->willReturn('de');
    }

    public function testHandle()
    {
        $filters = [
            'orderBy' => 'alphabetical',
        ];

        $this->nomenclatureItemsGetter
            ->getNomenclatureItems($this->sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $result1 = $this->prophesize(Result::class);
        $result2 = $this->prophesize(Result::class);
        $result1->getId()->willReturn(12);
        $result2->getId()->willReturn(71);
        $this->sheetSearchAdapter
            ->find(
                $this->event->reveal(),
                $filters,
                'alphabetical',
                'fr',
                [],
                [],
                [],
                null
            )->shouldBeCalled()
            ->willReturn([
                $result1->reveal(),
                $result2->reveal(),
            ])
        ;

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
        ];

        $this->sheetRepository
            ->findSheets(
                [new SheetIdView(12), new SheetIdView(71)]
            )->shouldBeCalled()
            ->willReturn($sheets)
        ;
        $sheetView1 = new SheetView('Exposant', [], [], 'President');
        $sheetView2 = new SheetView('Donneur d\'ordre', [], [], 'Directeur');
        $this->sheetViewQueryHandler
            ->handle(
                new SheetViewQuery(
                    $sheet1->reveal(),
                    $this->sheet->reveal(),
                    'fr',
                    'de',
                    true
                )
            )->shouldBeCalled()
            ->willReturn($sheetView1)
        ;

        $this->sheetViewQueryHandler
            ->handle(
                new SheetViewQuery(
                    $sheet2->reveal(),
                    $this->sheet->reveal(),
                    'fr',
                    'de',
                    true
                )
            )->shouldBeCalled()
            ->willReturn($sheetView2)
        ;

        $this->sheetViewQueryHandler->reMapFields($sheetView1)->shouldBeCalled();
        $this->sheetViewQueryHandler->reMapFields($sheetView2)->shouldBeCalled();

        $registrationFields = [
            'azerty123' => 'Titre de la fiche',
        ];
        $sheetFields = [
            'ytreza321' => 'Besoins',
        ];
        $this->sheetViewQueryHandler->getSheetRegistrationFields()->shouldBeCalled()->willReturn($registrationFields);
        $this->sheetViewQueryHandler->getSheetFields()->shouldBeCalled()->willReturn($sheetFields);

        $this->translator
            ->trans(SheetsViewQueryHandler::TRANS_COL_PARTICIPANT_POSITION, [], 'exports', 'fr')
            ->shouldBeCalled()
            ->willReturn('Fonction des participants')
        ;

        $this->translator->trans(SheetsViewQueryHandler::TRANS_COL_TYPE, [], 'exports', 'fr')
            ->shouldBeCalled()
            ->willReturn('Type de participation')
        ;

        $this->getViewedSheetsFromFilters->getFilteredByVisitSheetIds($filters, $this->user->reveal(), $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $handler = new SheetsViewQueryHandler(
            $this->translator->reveal(),
            $this->sheetSearchAdapter->reveal(),
            $this->nomenclatureItemsGetter->reveal(),
            $this->sheetRepository->reveal(),
            $this->sheetViewQueryHandler->reveal(),
            $this->getViewedSheetsFromFilters->reveal()
        );

        $query = new SheetsViewQuery(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $filters,
            'fr',
            [],
            [],
            true
        );
        $result = $handler->handle($query);

        $expected = new SheetListView(
            [$sheetView1, $sheetView2],
            $registrationFields,
            $sheetFields,
            'Fonction des participants',
            'Type de participation',
            true
        );

        $this->assertEquals($expected, $result);
    }
}
