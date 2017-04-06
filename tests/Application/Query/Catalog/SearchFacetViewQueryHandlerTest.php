<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Model\SearchFacet;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SearchFacetViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';

        $typeSearchFacet         = new SearchFacet($event, 'type', true);
        $keywordsSearchFacet     = new SearchFacet($event, 'keywords', true);
        $localizationSearchFacet = new SearchFacet($event, 'localization', false);

        // Expected
        $searchFacetTypeView         = new SearchFacetView('type', '', '', true);
        $searchFacetKeywordsView     = new SearchFacetView('keywords', '', '', true);
        $searchFacetLocalizationView = new SearchFacetView('localization', '', '', false);

        $expectedSearchFacetsView = new SearchFacetsView([
            $searchFacetTypeView,
            $searchFacetKeywordsView,
            $searchFacetLocalizationView,
        ]);

        // Mock
        $searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);

        $searchFacetRepository->getByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$typeSearchFacet, $keywordsSearchFacet, $localizationSearchFacet]);

        $handler = new SearchFacetViewQueryHandler($searchFacetRepository->reveal());

        $searchFacetsView = $handler->handle(new SearchFacetViewQuery($event, $locale));

        $this->assertEquals($searchFacetsView, $expectedSearchFacetsView);
        $this->assertEquals($searchFacetsView->hasType(), $searchFacetTypeView);
        $this->assertEquals($searchFacetsView->hasKeywords(), $searchFacetKeywordsView);
        $this->assertEquals($searchFacetsView->hasLocalization(), false);
    }
}
