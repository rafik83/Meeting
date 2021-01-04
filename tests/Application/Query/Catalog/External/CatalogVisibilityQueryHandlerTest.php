<?php

namespace Application\Query\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityQueryHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CatalogVisibilityQueryHandlerTest extends TestCase
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

        $query = new CatalogVisibilityQuery($this->event);
        $handler = new CatalogVisibilityQueryHandler($this->catalogVisibilityRepository->reveal());

        $handler->handle($query);
    }

    public function testHandleWithExistingCatalogVisibility()
    {
        $catalogVisibility = new CatalogVisibility($this->event);

        $this->catalogVisibilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn($catalogVisibility);

        $query = new CatalogVisibilityQuery($this->event);
        $handler = new CatalogVisibilityQueryHandler($this->catalogVisibilityRepository->reveal());

        $handler->handle($query);
    }
}
