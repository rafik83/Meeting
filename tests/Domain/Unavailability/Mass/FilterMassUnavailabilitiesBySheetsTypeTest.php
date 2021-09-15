<?php

namespace Proximum\Vimeet\Tests\Domain\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Unavailability\Mass\FilterMassUnavailabilitiesBySheetsType;
use PHPUnit\Framework\TestCase;

class FilterMassUnavailabilitiesBySheetsTypeTest extends TestCase
{
    public function testHandle()
    {
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getType()->willReturn($type1->reveal());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getType()->willReturn($type2->reveal());

        $mass1 = $this->prophesize(Mass::class);
        $mass1->hasType($type1)->shouldBeCalled()->willReturn(true);
        $mass1->hasType($type2)->shouldNotBeCalled();

        $mass2 = $this->prophesize(Mass::class);
        $mass2->hasType($type1)->shouldBeCalled()->willReturn(false);
        $mass2->hasType($type2)->shouldBeCalled()->willReturn(false);

        $mass3 = $this->prophesize(Mass::class);
        $mass3->hasType($type1)->shouldBeCalled()->willReturn(false);
        $mass3->hasType($type2)->shouldBeCalled()->willReturn(true);

        $filterMassUnavailabilitiesBySheetsType = new FilterMassUnavailabilitiesBySheetsType();
        $this->assertEquals(
            [
                $mass1->reveal(),
                $mass3->reveal(),
            ],
            $filterMassUnavailabilitiesBySheetsType
                ->handle(
                    [
                        $mass1->reveal(),
                        $mass2->reveal(),
                        $mass3->reveal()
                    ],
                    [
                        $sheet1->reveal(),
                        $sheet2->reveal(),
                    ]
                )
        );
    }
}
