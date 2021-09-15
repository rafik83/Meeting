<?php

namespace Proximum\Vimeet\Tests\Application\Query\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Nomenclature\GlobalNomenclatureViewQuery;
use Proximum\Vimeet\Application\Query\Nomenclature\GlobalNomenclatureViewQueryHandler;
use Proximum\Vimeet\Application\View\Nomenclature\GlobalNomenclatureView;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Nomenclature\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class GlobalNomenclatureViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
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

        // Mock
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $removeAuthorizationChecker = $this->prophesize(RemoveAuthorizationChecker::class);

        $nomenclatureRepository
            ->findGlobals()
            ->shouldBeCalled()
            ->willReturn([$nomenclature1->reveal(), $nomenclature2->reveal(), $nomenclature3->reveal()])
        ;
        $removeAuthorizationChecker->canBeRemoved($nomenclature1->reveal())->shouldBeCalled()->willReturn(true);
        $removeAuthorizationChecker->canBeRemoved($nomenclature2->reveal())->shouldBeCalled()->willReturn(false);
        $removeAuthorizationChecker->canBeRemoved($nomenclature3->reveal())->shouldBeCalled()->willReturn(false);

        // Expected
        $expected = [
            new GlobalNomenclatureView(1, 'title 1', 3, true),
            new GlobalNomenclatureView(2, 'title 2', 1, false),
            new GlobalNomenclatureView(3, 'title 3', 2, false),
        ];

        $globalNomenclatureViewQueryHandler = new GlobalNomenclatureViewQueryHandler(
            $nomenclatureRepository->reveal(),
            $removeAuthorizationChecker->reveal()
        );
        $result = $globalNomenclatureViewQueryHandler->handle(new GlobalNomenclatureViewQuery());

        $this->assertEquals($expected, $result);
    }
}
