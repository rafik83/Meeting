<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ProgramViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $eventFactory = new EventFactory();
        $sheetFactory = new SheetFactory();
        $user         = new User('test@test.com', 'azerty', 'password', 'fr');
        $event        = $eventFactory->createEvent();
        $sheet        = $sheetFactory->create($event, $user);

        $programView = new ProgramViewQuery($sheet, $user, 'fr');

        $happeningCategoryOne = $this->createHappeningCategoryMock($event, 'picto1', 1);
        $happeningCategoryTwo = $this->createHappeningCategoryMock($event, 'picto2', 2);

        $happeningCategoryOne->setTranslation(
            new Happening\CategoryTranslation($happeningCategoryOne, 'fr', 'title one')
        );
        $happeningCategoryTwo->setTranslation(
            new Happening\CategoryTranslation($happeningCategoryTwo, 'fr', 'title two')
        );

        $happenings = [
            new Happening($event, new \DateTime(), new \DateTime(), $happeningCategoryTwo, []),
            new Happening($event, new \DateTime(), new \DateTime(), $happeningCategoryOne, []),
        ];

        //Expected
        $linkViews = [
            new LinkView('title two'),
            new LinkView('title one'),
        ];

        $categoryViewExpected = new CategoryView('navigation.category.program', 'icon-PresFlash_2', $linkViews, true);

        // Mocks
        $happeningsAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $happeningsAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $happeningRepository->findListByEvent($event, 'fr')->shouldBeCalled()->willReturn($happenings);

        $programViewQueryHandler = new ProgramViewQueryHandler(
            $navigationBuilder->reveal(),
            $happeningsAccessChecker->reveal(),
            $happeningRepository->reveal()
        );

        $categoryView = $programViewQueryHandler->handle($programView);
        $this->assertEquals($categoryViewExpected, $categoryView);
    }

    /**
     * @param Event  $event
     * @param string $picto
     * @param int    $id
     *
     * @return Happening\Category
     */
    public function createHappeningCategoryMock(Event $event, $picto, $id)
    {
        $category    = new Happening\Category($event, $picto, 1, '#FFFFFF', '#FFFFFF');
        $reflection  = new \ReflectionClass(Happening\Category::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($category, $id);
        $property->setAccessible(false);

        return $category;
    }
}
