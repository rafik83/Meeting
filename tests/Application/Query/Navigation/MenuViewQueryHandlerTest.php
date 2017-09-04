<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\CategoryViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\MenuView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

class MenuViewQueryHandlerTest extends TestCase
{
    public function testHandleSheetNotFound()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);

        // Mock
        $categoryViewQueryHandler = $this->prophesize(CategoryViewQueryHandler::class);

        // Handler
        $handler = new MenuViewQueryHandler($categoryViewQueryHandler->reveal());
        $result = $handler->handle(new MenuViewQuery($event, 'fr', $sheet, $user));

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
        $categoryViewQueryHandler = $this->prophesize(CategoryViewQueryHandler::class);

        foreach (Category::$categories as $category) {
            $categoryViewQueryHandler
                ->handle(new CategoryViewQuery($sheet, $user, $category, 'fr'))
                ->shouldBeCalled()
                ->willReturn(new CategoryView('title', 'icon', [], true));
        }

        // Handler
        $handler = new MenuViewQueryHandler($categoryViewQueryHandler->reveal());
        $result = $handler->handle(new MenuViewQuery($event, 'fr', $sheet, $user));

        // Expected
        $categories = [];
        foreach (Category::$categories as $category) {
            $categories[] = new CategoryView('title', 'icon', [], true);
        }
        $expected = new MenuView($categories);

        // Assertion
        $this->assertEquals($expected, $result);
    }
}
