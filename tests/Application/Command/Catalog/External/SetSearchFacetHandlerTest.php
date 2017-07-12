<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacet;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacetHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use ReflectionClass;

class SetSearchFacetHandlerTest extends TestCase
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
        $searchFacet = new SearchFacet($this->event, 'type_1', true);

        $this->searchFacetRepository->add($searchFacet)->shouldBeCalled();

        $command = new SetSearchFacet([$searchFacet]);
        $handler = new SetSearchFacetHandler($this->searchFacetRepository->reveal());

        $handler->handle($command);
    }

    public function testHandleWithExistingSearchFacets()
    {
        $searchFacet = new SearchFacet($this->event, 'type', true);
        $reflectionClass = new ReflectionClass(SearchFacet::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($searchFacet, 1);
        $property->setAccessible(false);

        $this->searchFacetRepository->set($searchFacet)->shouldBeCalled();

        $command = new SetSearchFacet([$searchFacet]);
        $handler = new SetSearchFacetHandler($this->searchFacetRepository->reveal());

        $handler->handle($command);
    }
}
