<?php

namespace Proximum\Vimeet\Tests\Domain\Event\StaticFormulation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\StaticFormulation\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class DuplicatorTest extends TestCase
{
    public function testDuplicate(): void
    {
        $event = $this->prophesize(Event::class);
        $oldEvent = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'en']);
        $event->getDuplicatedFrom()->shouldBeCalled()->willReturn($oldEvent->reveal());

        $oldType1 = $this->prophesize(Type::class);
        $oldType2 = $this->prophesize(Type::class);
        $oldType3 = $this->prophesize(Type::class);
        $oldType1->getId()->willReturn(11);
        $oldType2->getId()->willReturn(12);
        $oldType3->getId()->willReturn(13);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);

        $storage = new DuplicatorDataStorage();
        $storage->types = [
            11 => $type1->reveal(),
            12 => $type2->reveal(),
            13 => $type3->reveal(),
        ];

        $oldStaticFormulation1 = $this->prophesize(StaticFormulation::class);
        $oldStaticFormulation2 = $this->prophesize(StaticFormulation::class);
        $oldStaticFormulation1
            ->getTypes()
            ->shouldBeCalled()
            ->willReturn([
                $oldType1->reveal(),
                $oldType2->reveal()
            ])
        ;
        $oldStaticFormulation1->getTitle('fr')->shouldBeCalled()->willReturn('Agenda');
        $oldStaticFormulation1->getTitle('en')->shouldBeCalled()->willReturn('Planning');
        $oldStaticFormulation1->getKey()->shouldBeCalled()->willReturn(Constant::STATIC_FORMULATION_KEY_AGENDA);
        $oldStaticFormulation2
            ->getTypes()
            ->shouldBeCalled()
            ->willReturn([
                $oldType3->reveal(),
            ])
        ;
        $oldStaticFormulation2->getKey()->shouldBeCalled()->willReturn(Constant::STATIC_FORMULATION_KEY_SHEET);
        $oldStaticFormulation2->getTitle('fr')->shouldBeCalled()->willReturn('Fiche de presentation');
        $oldStaticFormulation2->getTitle('en')->shouldBeCalled()->willReturn('My sheet');
        $oldStaticFormulations = [
            $oldStaticFormulation1->reveal(),
            $oldStaticFormulation2->reveal(),
        ];

        $staticFormulationRepository = $this->prophesize(StaticFormulationRepositoryInterface::class);
        $staticFormulationRepository->findByEvent($oldEvent->reveal())->shouldBeCalled()->willReturn($oldStaticFormulations);

        $newStaticFormulation1 = new StaticFormulation(
            $event->reveal(),
            Constant::STATIC_FORMULATION_KEY_AGENDA,
            [
                $type1->reveal(),
                $type2->reveal(),
            ]
        );
        $newStaticFormulation1->translate('fr', 'Agenda');
        $newStaticFormulation1->translate('en', 'Planning');
        $newStaticFormulation2 = new StaticFormulation(
            $event->reveal(),
            Constant::STATIC_FORMULATION_KEY_SHEET,
            [
                $type3->reveal(),
            ]
        );
        $newStaticFormulation2->translate('fr', 'Fiche de presentation');
        $newStaticFormulation2->translate('en', 'My sheet');

        $staticFormulationRepository->add($newStaticFormulation1)->shouldBeCalled();
        $staticFormulationRepository->add($newStaticFormulation2)->shouldBeCalled();

        $duplicator = new Duplicator($staticFormulationRepository->reveal());

        $duplicator->duplicate($event->reveal(), $storage);
    }
}
