<?php

namespace Proximum\Vimeet\Tests\Domain\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Catalog\SheetAccessChecker;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetAccessCheckerTest extends TestCase
{
    private $visibleParticipationTypes;

    public function setUp()
    {
        $this->visibleParticipationTypes = $this->prophesize(VisibleParticipationTypes::class);
    }

    public function testCheckAccess()
    {
        //Context
        $userSheet = $this->prophesize(Sheet::class);
        $requestedSheet1 = $this->prophesize(Sheet::class);
        $requestedSheet2 = $this->prophesize(Sheet::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $requestedSheet1->getType()->willReturn($type1);
        $requestedSheet2->getType()->willReturn($type2);
        $type1->getId()->willReturn(2);
        $type2->getId()->willReturn(3);

        // Mock
        $this->visibleParticipationTypes->getAllowedTypesList($userSheet->reveal())->shouldBeCalled()->willReturn([
            1 => 'Type 1',
            2 => 'Type 2',
        ]);

        // SheetAccessChecker
        $sheetAccessChecker = new SheetAccessChecker($this->visibleParticipationTypes->reveal());
        $result1 = $sheetAccessChecker->checkAccess($userSheet->reveal(), $requestedSheet1->reveal());
        $result2 = $sheetAccessChecker->checkAccess($userSheet->reveal(), $requestedSheet2->reveal());

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }
}
