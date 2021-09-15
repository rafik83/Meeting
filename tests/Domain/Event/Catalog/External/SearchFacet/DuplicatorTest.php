<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Catalog\External\SearchFacet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Domain\Event\Catalog\External\SearchFacet\Duplicator;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventDuplicated = EventFactory::createEvent('event duplicated');
        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );

        $searchFacet = new SearchFacet($eventDuplicated, 'type', true);
        $searchFacet->translate('fr', 'label fr', 'placeholder fr');
        $searchFacet->translate('en', 'label en', 'placeholder en');

        $searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $searchFacetRepository->add(Argument::that(
            function (SearchFacet $newSearchFacet) {
                return true;
            }
        ))->shouldBeCalled();

        $searchFacetRepository
            ->getByEvent($eventDuplicated)
            ->shouldBecalled()
            ->willReturn([$searchFacet]);

        (new Duplicator($searchFacetRepository->reveal()))->duplicate($event);
    }
}
