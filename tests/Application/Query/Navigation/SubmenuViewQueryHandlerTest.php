<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SubmenuViewQueryHandlerTest extends TestCase
{
    public function testCatalogHandle()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();

        $event->getConfiguration()->setDates(
            $datetime->modify('-1 month'),
            $datetime->modify('-1 month'),
            $datetime->modify('-1 month')
        );

        $user = new User('email@email.com', 'salt', 'password', 'fr');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInCatalog()->shouldBeCalled()->willReturn(true);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $locale = 'fr';
        $route = 'event_catalog_index';

        $query = new CatalogSubmenuViewQuery($user, $event, $locale, $sheet->reveal(), $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::CATALOG_ICON,
                'navigation.category.catalog',
                'navigation.category.catalog.link',
                true,
                false,
                true
            ),
            new SubmenuButtonView(
                Category::MEETING_ICON,
                'navigation.category.meeting',
                'navigation.category.meeting.link',
                false,
                false,
                true
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $navigationBuilder->getRoute('event_catalog_index', ['sheet' => 1])->shouldBeCalled()
            ->willReturn('navigation.category.catalog.link');

        $navigationBuilder->getRoute('event_meeting_list_request', ['sheet' => 1])->shouldBeCalled()
            ->willReturn('navigation.category.meeting.link');

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $datetime);
        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }

    public function testSheetHandle()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('email@email.com', 'salt', 'password', 'fr');
        $package = new Package($event, 'package', $datetime);
        $type->setPackage($package);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(2);

        $locale = 'fr';
        $route = 'event_catalog_index';

        $query = new SheetSubmenuViewQuery($user, $event, $locale, $sheet->reveal(), $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::SHEET_ICON,
                'sheet.title',
                'sheet.title.link',
                false,
                false,
                true
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $navigationBuilder->getRoute('event_sheet_default', ['sheet' => 2])->shouldBeCalled()
            ->willReturn('sheet.title.link');

        $handler = new SheetSubmenuViewQueryHandler($navigationBuilder->reveal());

        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }

    public function testAgendaHandle()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('email@email.com', 'salt', 'password', 'fr');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $package = new Package($event, 'package', $datetime);
        $type->setPackage($package);

        $locale = 'fr';
        $route = 'event_agenda';

        $query = new AgendaSubmenuViewQuery($user, $event, $locale, $sheet->reveal(), $route);

        // Expected
        $expectedSubmenuButtonViews = [
            new SubmenuButtonView(
                Category::AGENDA_ICON,
                'agenda.title',
                'agenda.title.link',
                true,
                false,
                true
            ),
            new SubmenuButtonView(
                Category::PROGRAM_ICON,
                'program.title',
                'program.title.link',
                false,
                false,
                true
            ),
        ];

        // Mock
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $happeningAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $agendaAccessChecker = $this->prophesize(AgendaAccessChecker::class);

        $happeningAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $agendaAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $navigationBuilder
            ->getRoute('event_agenda', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('agenda.title.link');

        $navigationBuilder
            ->getRoute('happening_program', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('program.title.link');

        $handler = new AgendaSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $happeningAccessChecker->reveal(),
            $agendaAccessChecker->reveal()
        );

        $menuButtonViews = $handler->handle($query);

        $this->assertEquals($expectedSubmenuButtonViews, $menuButtonViews);
    }
}
