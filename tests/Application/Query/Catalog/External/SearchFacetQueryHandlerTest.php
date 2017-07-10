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
        $expectedSearchFacet = new SearchFacet($this->event, 'type', true);
        $expectedSearchFacet->translate('fr', 'type', 'type');
        $query = new SearchFacetQuery($this->event);
        $handler = new SearchFacetQueryHandler($this->searchFacetRepository->reveal());

        $this->searchFacetRepository->getByEvent($this->event)->shouldBeCalled()->willReturn($expectedSearchFacet);

        $resultSearchFacet = $handler->handle($query);

        $this->assertEquals($expectedSearchFacet, $resultSearchFacet);
    }
}
