<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQueryHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityRegistrationUrlQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $catalogVisibility = $this->prophesize(CatalogVisibility::class);
        $catalogVisibility->getRegistrationUrl()->willReturn('https://vimeet.events');

        $catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepositoryInterface::class);

        $catalogVisibilityRepository->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn($catalogVisibility->reveal());

        $handler = new CatalogVisibilityRegistrationUrlQueryHandler(
            $catalogVisibilityRepository->reveal()
        );

        $result = $handler->handle(new CatalogVisibilityRegistrationUrlQuery($event->reveal()));

        $this->assertEquals('https://vimeet.events', $result);
    }
}
