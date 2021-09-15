<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CategoryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';

        $categoryOne = new Category($event);
        $categoryOne->translate($locale, 'category1');

        $categoryTwo = new Category($event);
        $categoryTwo->translate($locale, 'category2');

        $visibleCategories = [
            '1' => $categoryOne,
            '2' => $categoryTwo,
        ];

        $query = new CategoryViewQuery($event, $visibleCategories, 'fr');

        // Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);

        $categoryRepository->getCategoriesTitleByEventAndLocale(
            $query->event,
            $query->locale,
            $query->visibleCategories
        )->shouldBeCalled()->willReturn([
            '1' => 'category1',
            '2' => 'category2',
        ]);

        $handler       = new CategoryViewQueryHandler($categoryRepository->reveal());
        $categoryViews = $handler->handle($query);

        $exceptedTypeViews = [
            '1' => new CategoryView(1, 'category1', 0),
            '2' => new CategoryView(2, 'category2', 0),
        ];

        $this->assertEquals($exceptedTypeViews, $categoryViews);
    }
}
