<?php

namespace Proximum\Vimeet\Tests\Application\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class NomenclatureClonerTest extends TestCase
{
    public function testDuplicate()
    {
        $event        = EventFactory::createEvent();
        $nomenclature = new Nomenclature('title', 1, ['foobar' => ['label' => ['fr' => 'Foobar']]], true);
        $clone        = new Nomenclature('title', 1, ['foobar' => ['label' => ['fr' => 'Foobar']]], true, $event, $nomenclature);

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $nomenclatureRepository->add($clone)->shouldBeCalled();

        $cloner = new NomenclatureCloner($nomenclatureRepository->reveal());

        $this->assertEquals($clone, $cloner->duplicate($nomenclature, $event));
    }

    public function testDuplicateIfNotExistWhenCloneExists()
    {
        $event        = EventFactory::createEvent();
        $nomenclature = new Nomenclature('title', 1, ['foobar' => ['label' => ['fr' => 'Foobar']]], true);
        $clone        = new Nomenclature('title', 1, ['foobar' => ['label' => ['fr' => 'Foobar']]], true, $event, $nomenclature);

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $nomenclatureRepository->findClone($nomenclature, $event)->willReturn($clone);
        $nomenclatureRepository->add()->shouldNotBeCalled();

        $cloner = new NomenclatureCloner($nomenclatureRepository->reveal());

        $this->assertEquals($clone, $cloner->duplicateIfNotExists($nomenclature, $event));
    }

    public function testDuplicateIfNotExistWhenCloneNotExists()
    {
        $event        = EventFactory::createEvent();
        $nomenclature = new Nomenclature('title', 1, ['foobar' => ['label' => ['fr' => 'Foobar']]], true);
        $clone        = new Nomenclature('title', 1, ['foobar' => ['label' => ['fr' => 'Foobar']]], true, $event, $nomenclature);

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $nomenclatureRepository->findClone($nomenclature, $event)->willReturn(null);
        $nomenclatureRepository->add($clone)->shouldBeCalled();

        $cloner = new NomenclatureCloner($nomenclatureRepository->reveal());

        $this->assertEquals($clone, $cloner->duplicateIfNotExists($nomenclature, $event));
    }
}
