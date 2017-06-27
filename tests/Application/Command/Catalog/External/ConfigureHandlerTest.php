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
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\CatalogVisibilityRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfigureHandlerTest extends TestCase
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepository::class);
        $this->searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandle()
    {
        $searchFacet = new SearchFacet($this->event, 'type', true);
        $command     = new Configure($this->event, [$searchFacet]);

        $command->types = [
            new Type($this->event),
            new Type($this->event),
        ];

        $command->categories = [
            new Category($this->event),
            new Category($this->event),
        ];

        $this->searchFacetRepository->add($searchFacet)->shouldBeCalled();

        $this
            ->catalogVisibilityRepository
            ->add(new CatalogVisibility($this->event, $command->types, $command->categories))
            ->shouldBeCalled();

        $handler = new ConfigureHandler(
            $this->catalogVisibilityRepository->reveal(),
            $this->searchFacetRepository->reveal()
        );

        $handler->handle($command);
    }
}
