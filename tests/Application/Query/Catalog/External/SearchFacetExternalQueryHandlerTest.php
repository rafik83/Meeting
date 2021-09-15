<?php

namespace Application\Query\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Application\View\Catalog\TagFilterView;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SearchFacetExternalQueryHandlerTest extends TestCase
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    private $catalogTagFilterRepository;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $this->catalogTagFilterRepository = $this->prophesize(CatalogTagFilterRepositoryInterface::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandleWithCategory()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';

        $typeSearchFacet         = new SearchFacet($event, SearchFacet::TYPE_TYPE, true);
        $keywordsSearchFacet     = new SearchFacet($event, SearchFacet::TYPE_CATEGORY, true);
        $localizationSearchFacet = new SearchFacet($event, SearchFacet::TYPE_LOCALIZATION, false);

        // Expected
        $searchFacetTypeView         = new SearchFacetView(SearchFacet::TYPE_TYPE, '', '', true);
        $searchFacetCategoryView     = new SearchFacetView(SearchFacet::TYPE_CATEGORY, '', '', true);
        $searchFacetLocalizationView = new SearchFacetView(SearchFacet::TYPE_LOCALIZATION, '', '', false);

        $expectedSearchFacetsView = new SearchFacetsView(
            [
                $searchFacetTypeView,
                $searchFacetCategoryView,
                $searchFacetLocalizationView,
            ],
            [
                'sheet_organization_staff' => new TagFilterView('sheet_organization_staff', 'Effectif', 'Les effectifs'),
            ]
        );

        // Mock
        $this->searchFacetRepository->getByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$typeSearchFacet, $keywordsSearchFacet, $localizationSearchFacet]);

        $tagFilter = $this->prophesize(CatalogTagFilter::class);
        $tagFilter->getTag()->shouldBeCalled()->willReturn('sheet_organization_staff');
        $tagFilter->getLabel('fr')->shouldBeCalled()->willReturn('Effectif');
        $tagFilter->getPlaceholder('fr')->shouldBeCalled()->willReturn('Les effectifs');
        $this->catalogTagFilterRepository
            ->getByEventAndType($this->event, CatalogTagFilter::TYPE_EXTERNAL)
            ->shouldBeCalled()
            ->willReturn([
                $tagFilter->reveal()
            ])
        ;

        $handler = new SearchFacetExternalViewQueryHandler(
            $this->searchFacetRepository->reveal(),
            $this->catalogTagFilterRepository->reveal()
        );

        $searchFacetsView = $handler->handle(new SearchFacetExternalViewQuery($event, $locale));

        $this->assertEquals($searchFacetsView, $expectedSearchFacetsView);
        $this->assertTrue($searchFacetsView->hasType());
        $this->assertEquals($searchFacetsView->getCategory(), $searchFacetCategoryView);
        $this->assertNull($searchFacetsView->getLocalization());
    }
}
