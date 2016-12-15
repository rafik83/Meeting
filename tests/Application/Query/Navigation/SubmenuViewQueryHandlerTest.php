<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SubmenuViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCatalogHandle()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();

        $event->getConfiguration()->setDates(
            $datetime->modify('-1 month'),
            $datetime->modify('-1 month'),
            $datetime->modify('-1 month')
        );

        $type  = new Type($event);
        $user  = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, $datetime);
        $sheet->setInCatalog(true);

        $locale = 'fr';
        $route  = 'event_catalog_index';

        $query = new CatalogSubmenuViewQuery($user, $event, $locale, $sheet, $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::CATALOG_ICON,
                'navigation.category.catalog',
                'navigation.category.catalog.link',
                true
            ),
            new SubmenuButtonView(
                Category::MEETING_ICON,
                'navigation.category.meeting',
                'navigation.category.meeting.link',
                false
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $navigationBuilder->getRoute('event_catalog_index')->shouldBeCalled()
            ->willReturn('navigation.category.catalog.link');

        $navigationBuilder->getRoute('event_meeting_list_request', ['sheet' => null])->shouldBeCalled()
            ->willReturn('navigation.category.meeting.link');

        $handler         = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $datetime);
        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }

    public function testSheetHandle()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $package  = new Package($event, 'package', $datetime);
        $type->setPackage($package);

        $locale = 'fr';
        $route  = 'event_catalog_index';

        $query = new SheetSubmenuViewQuery($user, $event, $locale, $sheet, $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::SHEET_ICON,
                'sheet.title',
                'sheet.title.link',
                false
            ),
            new SubmenuButtonView(
                Category::PACKAGE_ICON,
                'package.title',
                'package.title.link',
                false,
                null
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $catalogAccessChecker = $this->prophesize(CatalogAccessChecker::class);
        $catalogAccessChecker->allowedToAccess($event)->shouldNotBeCalled();
        $happeningAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);

        $navigationBuilder->getRoute('event_sheet')->shouldBeCalled()
            ->willReturn('sheet.title.link');

        $navigationBuilder->getRoute('event_package')->shouldBeCalled()
            ->willReturn('package.title.link');


        $handler = new SheetSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartRowRepository->reveal(),
            $catalogAccessChecker->reveal(),
            $happeningAccessChecker->reveal()
        );

        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }

    public function testSheetHandleWithProgram()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $package  = new Package($event, 'package', $datetime);
        $type->setPackage($package);

        $locale = 'fr';
        $route  = 'event_catalog_index';

        $query = new SheetSubmenuViewQuery($user, $event, $locale, $sheet, $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::SHEET_ICON,
                'sheet.title',
                'sheet.title.link',
                false
            ),
            new SubmenuButtonView(
                Category::PROGRAM_ICON,
                'program.title',
                'program.title.link',
                false,
                null
            ),
            new SubmenuButtonView(
                Category::PACKAGE_ICON,
                'package.title',
                'package.title.link',
                false,
                null
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $catalogAccessChecker = $this->prophesize(CatalogAccessChecker::class);
        $catalogAccessChecker->allowedToAccess($event)->shouldNotBeCalled();
        $happeningAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $navigationBuilder->getRoute('event_sheet')->shouldBeCalled()
            ->willReturn('sheet.title.link');

        $navigationBuilder->getRoute('event_package')->shouldBeCalled()
            ->willReturn('package.title.link');

        $navigationBuilder->getRoute('happening_program')->shouldBeCalled()
            ->willReturn('program.title.link');


        $handler = new SheetSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartRowRepository->reveal(),
            $catalogAccessChecker->reveal(),
            $happeningAccessChecker->reveal()
        );

        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }

    public function testSheetHandleWithCatalogAndProgram()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $sheet->setInCatalog(true);
        $package  = new Package($event, 'package', $datetime);
        $type->setPackage($package);

        $locale = 'fr';
        $route  = 'event_catalog_index';

        $query = new SheetSubmenuViewQuery($user, $event, $locale, $sheet, $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::SHEET_ICON,
                'sheet.title',
                'sheet.title.link',
                false
            ),
            new SubmenuButtonView(
                Category::CATALOG_ICON,
                'catalog.title',
                'catalog.title.link',
                false,
                null
            ),
            new SubmenuButtonView(
                Category::PROGRAM_ICON,
                'program.title',
                'program.title.link',
                false,
                null
            ),
            new SubmenuButtonView(
                Category::PACKAGE_ICON,
                'package.title',
                'package.title.link',
                false,
                null
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $catalogAccessChecker = $this->prophesize(CatalogAccessChecker::class);
        $catalogAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $happeningAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $navigationBuilder->getRoute('event_sheet')->shouldBeCalled()
            ->willReturn('sheet.title.link');

        $navigationBuilder->getRoute('event_package')->shouldBeCalled()
            ->willReturn('package.title.link');

        $navigationBuilder->getRoute('event_catalog_index')->shouldBeCalled()
            ->willReturn('catalog.title.link');

        $navigationBuilder->getRoute('happening_program')->shouldBeCalled()
            ->willReturn('program.title.link');


        $handler = new SheetSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartRowRepository->reveal(),
            $catalogAccessChecker->reveal(),
            $happeningAccessChecker->reveal()
        );

        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }
}
