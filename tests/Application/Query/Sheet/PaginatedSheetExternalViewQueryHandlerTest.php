<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Catalog\PaginatedSheetExternalViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Catalog\PaginatedSheetExternalViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Catalog\CatalogSheetPreviewExternalView;
use Proximum\Vimeet\Domain\Catalog\ExternalCatalog;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PaginatedSheetExternalViewQueryHandlerTest extends TestCase
{
    /**
     * @dataProvider handleProvider
     *
     * @param Event  $event
     * @param string $locale
     * @param Sheet  $sheet1
     * @param Sheet  $sheet2
     */
    public function testHandle(Event $event, string $locale, Sheet $sheet1, Sheet $sheet2)
    {
        $page  = 1;
        $limit = 48;

        // Mock
        $sheetSearchAdapter                   = $this->prophesize(SheetSearchAdapterInterface::class);
        $sheetPreviewExternalViewQueryHandler = $this->prophesize(SheetPreviewExternalViewQueryHandler::class);

        $sheetSearchAdapter
            ->paginate(
                $event,
                ExternalCatalog::DEFAULT_FILTERS,
                Sheet\Constant::ORDER_BY_ALPHABETICAL,
                $page,
                $limit,
                $locale,
                true,
                []
            )
            ->shouldBeCalled()
            ->willReturn(new PaginatedResult([$sheet1, $sheet2], $page, $limit, 2))
        ;

        $sheet1Preview = new CatalogSheetPreviewExternalView(1, 'Elao', 'Fournisseur', [], $sheet1);

        $sheetPreviewExternalViewQueryHandler
            ->handle(new SheetPreviewExternalViewQuery($sheet1, $locale, $event, true))
            ->shouldBeCalled()
            ->willReturn($sheet1Preview);

        $sheet2Preview = new CatalogSheetPreviewExternalView(2, 'Vimeet', 'Investisseur', [], $sheet2);

        $sheetPreviewExternalViewQueryHandler
            ->handle(new SheetPreviewExternalViewQuery($sheet2, $locale, $event, true))
            ->shouldBeCalled()
            ->willReturn($sheet2Preview);

        $query = new PaginatedSheetExternalViewQuery($event, ExternalCatalog::DEFAULT_FILTERS, $page, $limit, $locale, true);
        $handler = new PaginatedSheetExternalViewQueryHandler(
            $sheetSearchAdapter->reveal(),
            $sheetPreviewExternalViewQueryHandler->reveal()
        );

        // Expected
        $expectedPaginatedResult = new PaginatedResult([$sheet1Preview, $sheet2Preview], $page, $limit, 2);

        $paginatedResult = $handler->handle($query);
        $this->assertEquals($expectedPaginatedResult->results, $paginatedResult->results);
        $this->assertEquals($expectedPaginatedResult->page, $paginatedResult->page);
        $this->assertEquals($expectedPaginatedResult->limit, $paginatedResult->limit);
    }

    /**
     * @return array
     */
    public function handleProvider(): array
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $type1  = $this->prophesize(Type::class);
        $type1->getTitle('fr')->willReturn('Fournisseur');
        $sheet1->getId()->willReturn(1);
        $sheet1->getTitle()->willReturn('Elao');
        $sheet1->getType()->willReturn($type1->reveal());

        $sheet2 = $this->prophesize(Sheet::class);
        $type2  = $this->prophesize(Type::class);
        $type2->getTitle('fr')->willReturn('Investisseur');
        $sheet2->getId()->willReturn(2);
        $sheet2->getTitle()->willReturn('Vimeet');
        $sheet2->getType()->willReturn($type2->reveal());

        return [
            [
                EventFactory::createEvent(),
                'fr',
                $sheet1->reveal(),
                $sheet2->reveal(),
            ],
        ];
    }
}
