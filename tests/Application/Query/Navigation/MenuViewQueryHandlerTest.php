<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Navigation;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Navigation\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\CategoryViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\MenuView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MenuViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleSheetNotFound()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();

        // Mock
        $sheetGuesser             = $this->prophesize(SheetGuesser::class);
        $categoryViewQueryHandler = $this->prophesize(CategoryViewQueryHandler::class);
        $sheetGuesser->getUserSheet($user, $event, 'fr')->shouldBeCalled()->willThrow(new SheetNotFoundException());

        // Handler
        $handler = new MenuViewQueryHandler($sheetGuesser->reveal(), $categoryViewQueryHandler->reveal());
        $result = $handler->handle(new MenuViewQuery($event, $user, 'fr'));

        $expected = new MenuView([]);
        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);

        // Mock
        $sheetGuesser             = $this->prophesize(SheetGuesser::class);
        $categoryViewQueryHandler = $this->prophesize(CategoryViewQueryHandler::class);
        $sheetGuesser->getUserSheet($user, $event, 'fr')->shouldBeCalled()->willReturn($sheet);
        foreach (Category::$categories as $category) {
            $categoryViewQueryHandler
                ->handle(new CategoryViewQuery($sheet, $user, $category, 'fr'))
                ->shouldBeCalled()
                ->willReturn(new CategoryView('title', 'icon', []));
        }

        // Handler
        $handler = new MenuViewQueryHandler($sheetGuesser->reveal(), $categoryViewQueryHandler->reveal());
        $result = $handler->handle(new MenuViewQuery($event, $user, 'fr'));

        // Expected
        $categories = [];
        foreach (Category::$categories as $category) {
            $categories[] = new CategoryView('title', 'icon', []);
        }
        $expected = new MenuView($categories);

        // Assertion
        $this->assertEquals($expected, $result);
    }
}
