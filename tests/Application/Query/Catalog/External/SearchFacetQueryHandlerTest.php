<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Catalog\External\SearchFacetQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\SearchFacetQueryHandler;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SearchFacetQueryHandlerTest extends TestCase
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandle()
    {
        $expectedSearchFacet = new SearchFacet($this->event, SearchFacet::TYPE_TYPE, true);
        $expectedSearchFacet->translate('fr', 'type', 'type');
        $query = new SearchFacetQuery($this->event);
        $handler = new SearchFacetQueryHandler($this->searchFacetRepository->reveal());

        $this->searchFacetRepository->getByEvent($this->event)->shouldBeCalled()->willReturn($expectedSearchFacet);

        $resultSearchFacet = $handler->handle($query);

        $this->assertEquals($expectedSearchFacet, $resultSearchFacet);
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

        $expectedSearchFacetsView = new SearchFacetsView([
            $searchFacetTypeView,
            $searchFacetCategoryView,
            $searchFacetLocalizationView,
        ]);

        // Mock
        $searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);

        $searchFacetRepository->getByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$typeSearchFacet, $keywordsSearchFacet, $localizationSearchFacet]);

        $handler = new SearchFacetExternalViewQueryHandler($searchFacetRepository->reveal());

        $searchFacetsView = $handler->handle(new SearchFacetExternalViewQuery($event, $locale));

        $this->assertEquals($searchFacetsView, $expectedSearchFacetsView);
        $this->assertEquals($searchFacetsView->hasType(), false);
        $this->assertEquals($searchFacetsView->getCategory(), $searchFacetCategoryView);
        $this->assertEquals($searchFacetsView->getLocalization(), null);
    }
}
