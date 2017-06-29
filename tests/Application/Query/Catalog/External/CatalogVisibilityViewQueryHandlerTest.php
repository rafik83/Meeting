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
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CatalogVisibilityViewQueryHandlerTest extends TestCase
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepositoryInterface::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandle()
    {
        $this->catalogVisibilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn(null);

        $query = new CatalogVisibilityViewQuery($this->event);
        $handler = new CatalogVisibilityViewQueryHandler($this->catalogVisibilityRepository->reveal());

        $handler->handle($query);
    }

    public function testHandleWithExistingCatalogVisibility()
    {
        $catalogVisibility = new CatalogVisibility($this->event);

        $this->catalogVisibilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn($catalogVisibility);

        $query = new CatalogVisibilityViewQuery($this->event);
        $handler = new CatalogVisibilityViewQueryHandler($this->catalogVisibilityRepository->reveal());

        $handler->handle($query);
    }
}
