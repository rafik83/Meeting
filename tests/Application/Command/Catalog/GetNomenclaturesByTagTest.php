<?php

namespace Proximum\Vimeet\Tests\Application\Command\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class GetNomenclaturesByTagTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $nomenclature1 = $this->prophesize(Nomenclature::class);
        $nomenclature2 = $this->prophesize(Nomenclature::class);

        $taggedNomenclatureFilter = $this->prophesize(TaggedNomenclatureFilter::class);
        $taggedNomenclatureFilter->getNomenclaturesId()->willReturn([42, 1337]);

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $nomenclatureRepository
            ->findByEventAndIds($event->reveal(), [42, 1337])
            ->shouldBeCalled()
            ->willReturn([$nomenclature1->reveal(), $nomenclature2->reveal()])
        ;

        $taggedNomenclatureFilterRepository = $this->prophesize(TaggedNomenclatureFilterRepositoryInterface::class);
        $taggedNomenclatureFilterRepository
            ->getByEventAndTag($event->reveal(), 'my_tag')
            ->shouldBeCalled()
            ->willReturn($taggedNomenclatureFilter->reveal())
        ;

        $getNomenclaturesByTag = new GetNomenclaturesByTag(
            $nomenclatureRepository->reveal(),
            $taggedNomenclatureFilterRepository->reveal()
        );
        $this->assertEquals(
            [
                $nomenclature1->reveal(),
                $nomenclature2->reveal(),
            ],
            $getNomenclaturesByTag->handle($event->reveal(), 'my_tag')
        );
    }
}
