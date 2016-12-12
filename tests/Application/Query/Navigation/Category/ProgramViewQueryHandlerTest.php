<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ProgramViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $eventFactory = new EventFactory();
        $sheetFactory = new SheetFactory();
        $user         = new User('test@test.com', 'azerty', 'password', 'fr');
        $event        = $eventFactory->createEvent();
        $sheet        = $sheetFactory->create($event, $user);

        $programView = new ProgramViewQuery($sheet, $user, 'fr');

        $happeningCategoryOne = new Happening\Category($event, 'picto1', 1, '#FFFFFF', '#FFFFFF');
        $happeningCategoryTwo = new Happening\Category($event, 'picto2', 2, '#FFFFFF', '#FFFFFF');

        $happeningCategoryOne->setTranslation(
            new Happening\CategoryTranslation($happeningCategoryOne, 'fr', 'title one')
        );
        $happeningCategoryTwo->setTranslation(
            new Happening\CategoryTranslation($happeningCategoryTwo, 'fr', 'title two')
        );

        $happenings = [
            new Happening($event, new \DateTime(), new \DateTime(), $happeningCategoryTwo),
            new Happening($event, new \DateTime(), new \DateTime(), $happeningCategoryOne)
        ];

        //Expected
        $linkViews = [
            new LinkView('title one'),
            new LinkView('title two'),
        ];

        $categoryViewExpected = new CategoryView('navigation.category.program', 'icon-Calendrier', $linkViews);

        // Mocks
        $happeningsAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $happeningsAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $happeningRepository->findListByEvent($event, "fr")->shouldBeCalled()->willReturn($happenings);

        $programViewQueryHandler = new ProgramViewQueryHandler(
            $navigationBuilder->reveal(),
            $happeningsAccessChecker->reveal(),
            $happeningRepository->reveal()
        );

        $categoryView = $programViewQueryHandler->handle($programView);
        $this->assertEquals($categoryViewExpected, $categoryView);
    }
}
