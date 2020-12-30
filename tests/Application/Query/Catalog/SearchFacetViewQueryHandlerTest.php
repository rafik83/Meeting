<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Application\View\Catalog\TagFilterView;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SearchFacetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';

        $typeSearchFacet         = new SearchFacet($event, SearchFacet::TYPE_TYPE, true);
        $keywordsSearchFacet     = new SearchFacet($event, SearchFacet::TYPE_KEYWORDS, true);
        $localizationSearchFacet = new SearchFacet($event, SearchFacet::TYPE_LOCALIZATION, false);

        // Expected
        $searchFacetTypeView         = new SearchFacetView(SearchFacet::TYPE_TYPE, '', '', true);
        $searchFacetKeywordsView     = new SearchFacetView(SearchFacet::TYPE_KEYWORDS, '', '', true);
        $searchFacetLocalizationView = new SearchFacetView(SearchFacet::TYPE_LOCALIZATION, '', '', false);

        $expectedSearchFacetsView = new SearchFacetsView(
            [
                $searchFacetTypeView,
                $searchFacetKeywordsView,
                $searchFacetLocalizationView,
            ],
            [
                'sheet_organization_category' => new TagFilterView(
                    'sheet_organization_category',
                    'organization category label',
                    'organization category placeholder'
                )
            ]
        );

        // Mock
        $searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $catalogTagFilterRepository = $this->prophesize(CatalogTagFilterRepositoryInterface::class);

        $searchFacetRepository->getByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$typeSearchFacet, $keywordsSearchFacet, $localizationSearchFacet]);

        $catalogTagFilter = $this->prophesize(CatalogTagFilter::class);
        $catalogTagFilter->getTag()->shouldBeCalled()->willReturn('sheet_organization_category');
        $catalogTagFilter->getLabel('fr')->shouldBeCalled()->willReturn('organization category label');
        $catalogTagFilter->getPlaceholder('fr')->shouldBeCalled()->willReturn('organization category placeholder');
        $catalogTagFilterRepository
            ->getByEventAndType($event, CatalogTagFilter::TYPE_INTERNAL)
            ->shouldBeCalled()
            ->willReturn([$catalogTagFilter->reveal()])
        ;

        $handler = new SearchFacetViewQueryHandler(
            $searchFacetRepository->reveal(),
            $catalogTagFilterRepository->reveal()
        );

        $searchFacetsView = $handler->handle(new SearchFacetViewQuery($event, $locale));

        $this->assertEquals($searchFacetsView, $expectedSearchFacetsView);
        $this->assertEquals($searchFacetsView->hasType(), true);
        $this->assertEquals($searchFacetsView->getKeywords(), $searchFacetKeywordsView);
        $this->assertEquals($searchFacetsView->getLocalization(), null);
    }
}
