<?php

namespace Proximum\Vimeet\Tests\Domain\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class TaggedNomenclatureFilterGetterTest extends TestCase
{
    public function testGetNomenclaturesItemsByEvent()
    {
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->willReturn('fr');

        $nomenclatureItem1 = $this->prophesize(NomenclatureItem::class);
        $nomenclatureItem1->getKey()->shouldBeCalled()->willReturn('u58b57c0ecbdb3');
        $nomenclatureItem1->getLabel('fr')->shouldBeCalled()->willReturn('dribble');

        $nomenclatureItem2 = $this->prophesize(NomenclatureItem::class);
        $nomenclatureItem2->getKey()->shouldBeCalled()->willReturn('u58b57c0ecbf13');
        $nomenclatureItem2->getLabel('fr')->shouldBeCalled()->willReturn('vista');

        $nomenclatureItem3 = $this->prophesize(NomenclatureItem::class);
        $nomenclatureItem3->getKey()->shouldBeCalled()->willReturn('u58b57c0ecbf56');
        $nomenclatureItem3->getLabel('fr')->shouldBeCalled()->willReturn('puissance');

        $nomenclatureItem4 = $this->prophesize(NomenclatureItem::class);
        $nomenclatureItem4->getKey()->shouldBeCalled()->willReturn('u58b57c0ecbf55');
        $nomenclatureItem4->getLabel('fr')->shouldBeCalled()->willReturn('plongeons');

        $nomenclature = $this->prophesize(Nomenclature::class);
        $nomenclature->getId()->shouldBeCalled()->willReturn(1);
        $nomenclature->getTitle()->shouldBeCalled()->willReturn('mbappe');
        $nomenclature->getLastLevel()->shouldBeCalled()->willReturn([$nomenclatureItem1->reveal()]);
        $nomenclature2 = $this->prophesize(Nomenclature::class);
        $nomenclature2->getId()->shouldBeCalled()->willReturn(2);
        $nomenclature2->getTitle()->shouldBeCalled()->willReturn('neymar');
        $nomenclature2->getLastLevel()->shouldBeCalled()->willReturn([$nomenclatureItem1->reveal(), $nomenclatureItem2->reveal()]);
        $nomenclature3 = $this->prophesize(Nomenclature::class);
        $nomenclature3->getId()->shouldBeCalled()->willReturn(3);
        $nomenclature3->getTitle()->shouldBeCalled()->willReturn('ronaldo');
        $nomenclature3->getLastLevel()->shouldBeCalled()->willReturn([$nomenclatureItem3->reveal(), $nomenclatureItem4->reveal()]);

        $taggedNomenclatureFilter = $this->prophesize(TaggedNomenclatureFilter::class);
        $taggedNomenclatureFilter->getNomenclaturesId()->shouldBeCalled()->willReturn([1, 2]);
        $taggedNomenclatureFilter->getTag()->shouldBeCalled()->willReturn('tag');
        $taggedNomenclatureFilter2 = $this->prophesize(TaggedNomenclatureFilter::class);
        $taggedNomenclatureFilter2->getNomenclaturesId()->shouldBeCalled()->willReturn([1, 3]);
        $taggedNomenclatureFilter2->getTag()->shouldBeCalled()->willReturn('tag2');

        $taggedNomenclatureFilterRepository = $this->prophesize(TaggedNomenclatureFilterRepositoryInterface::class);
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);

        $nomenclatureRepository->findByEventAndIds($event->reveal(), [1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([$nomenclature->reveal(), $nomenclature2->reveal(), $nomenclature3->reveal()]);

        $taggedNomenclatureFilterRepository->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$taggedNomenclatureFilter->reveal(), $taggedNomenclatureFilter2->reveal()]);

        $taggedNomenclatureFilterGetter = new TaggedNomenclatureFilterGetter($taggedNomenclatureFilterRepository->reveal(), $nomenclatureRepository->reveal());
        $result = $taggedNomenclatureFilterGetter->getNomenclaturesItemsByEvent($event->reveal(), 'fr');

        $expectedResult = [
            1 => new NomenclatureFilterView(1, 'mbappe', ['u58b57c0ecbdb3' => 'dribble'], [0 => 'tag', 1 => 'tag2']),
            2 => new NomenclatureFilterView(2, 'neymar', ['u58b57c0ecbdb3' => 'dribble', 'u58b57c0ecbf13' => 'vista'], [0 => 'tag']),
            3 => new NomenclatureFilterView(3, 'ronaldo', ['u58b57c0ecbf56' => 'puissance', 'u58b57c0ecbf55' => 'plongeons'], [0 => 'tag2']),
        ];

        $this->assertEquals($result, $expectedResult);
    }
}
