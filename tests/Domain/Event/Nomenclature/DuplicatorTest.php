<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Nomenclature\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
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
        $nomenclature    = $this->prophesize(Nomenclature::class);
        $nomenclature->getId()->shouldBeCalled()->willReturn(2);

        $newNomenclature = new Nomenclature('new nomenclature test');

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $nomenclatureRepository->add($newNomenclature)->shouldBeCalled();
        $nomenclatureRepository
            ->findByEvent($eventDuplicated)
            ->shouldBeCalled()
            ->willReturn([$nomenclature]);

        $nomenclatureCloner = $this->prophesize(NomenclatureCloner::class);
        $nomenclatureCloner
            ->duplicate($nomenclature, $event)
            ->shouldBeCalled()
            ->willReturn($newNomenclature);

        $duplicatorDataStorage = (new Duplicator(
            $nomenclatureRepository->reveal(),
            $nomenclatureCloner->reveal()
        ))->duplicate($event, new DuplicatorDataStorage());

        $this->assertEquals($newNomenclature, $duplicatorDataStorage->nomenclatures[2]);
    }
}
