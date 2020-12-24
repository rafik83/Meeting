<?php

namespace Proximum\Vimeet\Tests\Application\Query\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Category\MeetingCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Category\MeetingCategoryViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class MeetingCategoryViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $categoryRepository;

    /** @var MeetingCategoryViewQueryHandler */
    private $handler;

    /** @var Event */
    private $event;

    /** @var Sheet */
    private $sheet;

    /** @var MeetingCategoryViewQuery */
    private $query;

    /** @var Category */
    private $category;

    /** @var string */
    private $locale;

    /** @var ObjectProphecy */
    private $searchFacetViewQueryHandler;

    /** @var SearchFacetViewQuery */
    private $searchFacetViewQuery;

    public function setUp()
    {
        $this->categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $this->searchFacetViewQueryHandler    = $this->prophesize(SearchFacetViewQueryHandler::class);
        $this->locale                         = 'fr';

        $this->handler = new MeetingCategoryViewQueryHandler(
            $this->categoryRepository->reveal(),
            $this->searchFacetViewQueryHandler->reveal()
        );

        $this->event = EventFactory::createEvent();
        $this->sheet = SheetFactory::create($this->event);
        $this->query = new MeetingCategoryViewQuery($this->sheet, $this->locale);

        $this->searchFacetViewQuery = new SearchFacetViewQuery($this->event, $this->locale);
        $this->category             = $this->prophesize(Category::class);
    }

    public function testHandleCategorySearchFacetDisabled()
    {
        $searchFacetView  = new SearchFacetView('category', 'catégorie', 'catégorie', false);
        $searchFacetsView = new SearchFacetsView([$searchFacetView]);

        $this->searchFacetViewQueryHandler
            ->handle($this->searchFacetViewQuery)
            ->shouldBeCalled()
            ->willReturn($searchFacetsView);

        $this->assertEmpty($this->handler->handle($this->query));
    }

    public function testHandle()
    {
        $searchFacetView  = new SearchFacetView('category', 'catégorie', 'catégorie', true);
        $searchFacetsView = new SearchFacetsView([$searchFacetView]);

        $this->category->getId()->shouldBeCalled()->willReturn(1);
        $this->category->getTitle('fr')->shouldBeCalled()->willReturn('category title');

        $expectedCategoryView = new CategoryView(1, 'category title');

        $this->searchFacetViewQueryHandler
            ->handle($this->searchFacetViewQuery)
            ->shouldBeCalled()
            ->willReturn($searchFacetsView);

        $this->categoryRepository
            ->getFromSheetMeetingRequests($this->sheet, $this->locale)
            ->shouldBeCalled()
            ->willReturn([$this->category]);

        $categoryViews = $this->handler->handle($this->query);
        $this->assertEquals([$expectedCategoryView], $categoryViews);
    }
}
