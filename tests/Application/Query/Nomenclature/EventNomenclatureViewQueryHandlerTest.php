<?php

namespace Proximum\Vimeet\Tests\Application\Query\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Nomenclature\EventNomenclatureViewQuery;
use Proximum\Vimeet\Application\Query\Nomenclature\EventNomenclatureViewQueryHandler;
use Proximum\Vimeet\Application\View\Nomenclature\EventNomenclatureView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Nomenclature\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class EventNomenclatureViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $nomenclature1 = $this->prophesize(Nomenclature::class);
        $nomenclature2 = $this->prophesize(Nomenclature::class);
        $nomenclature3 = $this->prophesize(Nomenclature::class);
        $nomenclature1->getId()->willReturn(1);
        $nomenclature2->getId()->willReturn(2);
        $nomenclature3->getId()->willReturn(3);
        $nomenclature1->getTitle()->willReturn('title 1');
        $nomenclature2->getTitle()->willReturn('title 2');
        $nomenclature3->getTitle()->willReturn('title 3');
        $nomenclature1->getDepth()->willReturn(3);
        $nomenclature2->getDepth()->willReturn(1);
        $nomenclature3->getDepth()->willReturn(2);
        $nomenclature1->getEvent()->willReturn($event->reveal());
        $nomenclature2->getEvent()->willReturn($event->reveal());
        $nomenclature3->getEvent()->willReturn($event->reveal());

        // Mock
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $removeAuthorizationChecker = $this->prophesize(RemoveAuthorizationChecker::class);

        $nomenclatureRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$nomenclature1->reveal(), $nomenclature2->reveal(), $nomenclature3->reveal()])
        ;
        $removeAuthorizationChecker->canBeRemoved($nomenclature1->reveal())->shouldBeCalled()->willReturn(true);
        $removeAuthorizationChecker->canBeRemoved($nomenclature2->reveal())->shouldBeCalled()->willReturn(false);
        $removeAuthorizationChecker->canBeRemoved($nomenclature3->reveal())->shouldBeCalled()->willReturn(false);

        // Expected
        $expected = [
            new EventNomenclatureView(1, 'title 1', 3, true, $event->reveal()),
            new EventNomenclatureView(2, 'title 2', 1, false, $event->reveal()),
            new EventNomenclatureView(3, 'title 3', 2, false, $event->reveal()),
        ];

        $eventNomenclatureViewQueryHandler = new EventNomenclatureViewQueryHandler(
            $nomenclatureRepository->reveal(),
            $removeAuthorizationChecker->reveal()
        );
        $result = $eventNomenclatureViewQueryHandler->handle(new EventNomenclatureViewQuery($event->reveal()));

        $this->assertEquals($expected, $result);
    }
}
