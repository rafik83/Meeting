<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Catalog\CatalogSheetPreviewExternalView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetPreviewExternalViewQueryHandlerTest extends TestCase
{
    /**
     * @dataProvider handleProvider
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param Event  $event
     */
    public function testHandle(Sheet $sheet, string $locale, Event $event)
    {
        // Mock
        $preview = $this->prophesize(Preview::class);

        $preview->getPreview($sheet, $locale)
            ->shouldBeCalled()
            ->willReturn([]);

        $query   = new SheetPreviewExternalViewQuery($sheet, $locale, $event, false);
        $handler = new SheetPreviewExternalViewQueryHandler($preview->reveal());

        // Expected
        $expectedPreviewExternalView = new CatalogSheetPreviewExternalView(1, 'Proximum', 'Fournisseur', [], $sheet);

        $sheetPreviewExternalView = $handler->handle($query);
        $this->assertEquals($expectedPreviewExternalView, $sheetPreviewExternalView);
    }

    /**
     * @dataProvider handleProvider
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param Event  $event
     */
    public function testHandleWithCategory(Sheet $sheet, string $locale, Event $event)
    {
        $preview = $this->prophesize(Preview::class);

        $preview->getPreview($sheet, $locale)
            ->shouldBeCalled()
            ->willReturn([]);

        $query = new SheetPreviewExternalViewQuery($sheet, $locale, $event, true);
        $handler = new SheetPreviewExternalViewQueryHandler($preview->reveal());

        $expectedPreviewExternalView = new CatalogSheetPreviewExternalView(1, 'Proximum', 'Fournisseur, Exposant', [], $sheet);

        $sheetPreviewExternalView = $handler->handle($query);
        $this->assertEquals($expectedPreviewExternalView, $sheetPreviewExternalView);
    }

    /**
     * @return array
     */
    public function handleProvider(): array
    {
        $event = EventFactory::createEvent();
        $type  = $this->prophesize(Type::class);
        $type->getTitle('fr')->willReturn('Fournisseur');
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $sheet->getTitle()->willReturn('Proximum');
        $sheet->getType()->willReturn($type->reveal());
        $type->getCategoriesTitles('fr')->willReturn(['Fournisseur', 'Exposant']);

        return [
            [
                $sheet->reveal(),
                'fr',
                $event,
            ],
        ];
    }
}
