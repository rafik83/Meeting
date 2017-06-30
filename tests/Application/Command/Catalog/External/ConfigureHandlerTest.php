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
use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureHandler;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacet;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacetHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\CatalogVisibilityRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfigureHandlerTest extends TestCase
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;
    
    /** @var SetSearchFacetHandler */
    private $setSearchFacetHandler;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepository::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->setSearchFacetHandler = $this->prophesize(SetSearchFacetHandler::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandle()
    {
        $catalogVisibility = new CatalogVisibility($this->event);
        $searchFacet = new SearchFacet($this->event, 'type', true);
        $command     = new Configure($this->event, $catalogVisibility, [$searchFacet]);

        $this
            ->catalogVisibilityRepository
            ->getByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->catalogVisibilityRepository->add($catalogVisibility)->shouldBeCalled();

        $this->setSearchFacetHandler->handle(new SetSearchFacet([$searchFacet]))->shouldBeCalled();

        $this->eventRepository->set($this->event)->shouldBeCalled();

        $handler = new ConfigureHandler(
            $this->catalogVisibilityRepository->reveal(),
            $this->eventRepository->reveal(),
            $this->setSearchFacetHandler->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithExistingCatalogVisibility()
    {
        $catalogVisibility = new CatalogVisibility($this->event);
        $searchFacet = new SearchFacet($this->event, 'type', true);
        $command     = new Configure($this->event, $catalogVisibility, [$searchFacet]);

        $this
            ->catalogVisibilityRepository
            ->getByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn($catalogVisibility);

        $this->catalogVisibilityRepository->set($catalogVisibility)->shouldBeCalled();

        $this->setSearchFacetHandler->handle(new SetSearchFacet([$searchFacet]))->shouldBeCalled();

        $this->eventRepository->set($this->event)->shouldBeCalled();

        $handler = new ConfigureHandler(
            $this->catalogVisibilityRepository->reveal(),
            $this->eventRepository->reveal(),
            $this->setSearchFacetHandler->reveal()
        );

        $handler->handle($command);
    }
}
