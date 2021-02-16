<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\LocaleHelperInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Category\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class SheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $locale = 'fr';
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getId()->willReturn(2);
        $event->getLocales()->willReturn(['fr', 'en', 'de']);

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilder::class);
        $localeHelper = $this->prophesize(LocaleHelperInterface::class);
        $localeHelper->language('fr', 'fr')->shouldBeCalled()->willReturn('Français');
        $localeHelper->language('en', 'fr')->shouldBeCalled()->willReturn('Anglais');
        $localeHelper->language('de', 'fr')->shouldBeCalled()->willReturn('Allemand');
        $navigationBuilder
            ->getRoute(SheetViewQueryHandler::EVENT_SHEET_ROUTE, ['sheet' => 2, 'locale' => 'fr'])
            ->shouldBeCalled()
            ->willReturn('/fr/sheet/fr')
        ;
        $navigationBuilder
            ->getRoute(SheetViewQueryHandler::EVENT_SHEET_ROUTE, ['sheet' => 2, 'locale' => 'en'])
            ->shouldBeCalled()
            ->willReturn('/fr/sheet/en')
        ;
        $navigationBuilder
            ->getRoute(SheetViewQueryHandler::EVENT_SHEET_ROUTE, ['sheet' => 2, 'locale' => 'de'])
            ->shouldBeCalled()
            ->willReturn('/fr/sheet/de')
        ;

        // Handler
        $sheetViewQueryHandler = new SheetViewQueryHandler($navigationBuilder->reveal(), $localeHelper->reveal());
        $result = $sheetViewQueryHandler->handle(new SheetViewQuery($sheet->reveal(), $user->reveal(), $locale));

        $expected = new CategoryView(
            Category::SHEET,
            Category::SHEET_ICON,
            [
                new LinkView('Français', '/fr/sheet/fr', 'fr'),
                new LinkView('Anglais', '/fr/sheet/en', 'en'),
                new LinkView('Allemand', '/fr/sheet/de', 'de'),
            ],
            true
        );

        $this->assertEquals($expected, $result);
    }
}
