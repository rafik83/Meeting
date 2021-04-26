<?php

namespace Proximum\Vimeet\Tests\Application\Query\CustomLink;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\CustomLink\CustomLinkListView;
use Proximum\Vimeet\Application\Query\CustomLink\CustomLinkListViewQuery;
use Proximum\Vimeet\Application\Query\CustomLink\CustomLinkListViewQueryHandler;
use Proximum\Vimeet\Application\Query\CustomLink\CustomLinkView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;

class CustomLinkListViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // fixtures

        $event = $this->prophesize(Event::class);

        $type1 = $this->prophesize(Type::class);
        $type1->getTitle('en')->willReturn('Seller');

        $type2 = $this->prophesize(Type::class);
        $type2->getTitle('en')->willReturn('Buyer');

        $customLink1 = $this->prophesize(Event\CustomLink::class);
        $customLink1->getId()->willReturn(42);
        $customLink1->getLabel('en')->willReturn('External link 1');
        $customLink1->getTypes()->willReturn([$type1->reveal()]);
        $customLink1->getPriority()->willReturn(0);
        $customLink1->getUrl()->willReturn('https://example.org/1');

        $customLink2 = $this->prophesize(Event\CustomLink::class);
        $customLink2->getId()->willReturn(66);
        $customLink2->getLabel('en')->willReturn('External link 2');
        $customLink2->getTypes()->willReturn([$type1->reveal(), $type2->reveal(),]);
        $customLink2->getPriority()->willReturn(1);
        $customLink2->getUrl()->willReturn('https://example.org/2');

        // dependency's prophesizes

        $customLinkRepository = $this->prophesize(CustomLinkRepositoryInterface::class);
        $customLinkRepository->findByEvent($event->reveal())
            ->willReturn([$customLink1->reveal(), $customLink2->reveal()])
        ;

        // run test

        $query = new CustomLinkListViewQuery($event->reveal(), 'en');

        $handler = new CustomLinkListViewQueryHandler($customLinkRepository->reveal());
        $result = $handler->handle($query);

        $expectedCustomLinkViews = [
            new CustomLinkView(42, 'External link 1', 'https://example.org/1', ['Seller'], 0),
            new CustomLinkView(66, 'External link 2', 'https://example.org/2', ['Seller', 'Buyer'], 1),
        ];
        $expectedResult = new CustomLinkListView($expectedCustomLinkViews);

        self::assertEquals($expectedResult, $result);
    }
}
