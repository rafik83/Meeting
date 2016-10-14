<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Application\Command\Event\SearchFacet\Update;
use Proximum\Vimeet\Application\Command\Event\SearchFacet\UpdateHandler;
use Proximum\Vimeet\Domain\Model\SearchFacet;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        $searchFacets = [
            new SearchFacet($event, 'structure', true),
            new SearchFacet($event, 'type', false),
            new SearchFacet($event, 'keywords', true),
        ];

        $update = new Update($searchFacets);

        $expectedFacetOne   = new SearchFacet($event, 'structure', true);
        $expectedFacetTwo   = new SearchFacet($event, 'type', false);
        $expectedFacetThree = new SearchFacet($event, 'keywords', true);

        $searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $searchFacetRepository->add($expectedFacetOne)->shouldBeCalled();
        $searchFacetRepository->add($expectedFacetTwo)->shouldBeCalled();
        $searchFacetRepository->add($expectedFacetThree)->shouldBeCalled();

        $handler = new UpdateHandler($searchFacetRepository->reveal());
        $handler->handle($update);
    }
}
