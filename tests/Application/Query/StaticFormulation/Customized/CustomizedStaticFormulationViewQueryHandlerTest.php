<?php

namespace Proximum\Vimeet\Tests\Application\Query\StaticFormulation\Customized;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\StaticFormulation\Customized\CustomizedStaticFormulationViewQuery;
use Proximum\Vimeet\Application\Query\StaticFormulation\Customized\CustomizedStaticFormulationViewQueryHandler;
use Proximum\Vimeet\Application\View\StaticFormulation\Customized\CustomizedStaticFormulationView;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class CustomizedStaticFormulationViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $key = Constant::STATIC_FORMULATION_KEY_SHEET;
        $id = 123;

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type1->getId()->shouldBeCalled()->willReturn(11);
        $type2->getId()->shouldBeCalled()->willReturn(12);
        $type1->getTitle('fr')->shouldBeCalled()->willReturn('Type 1');
        $type2->getTitle('fr')->shouldBeCalled()->willReturn('Type 2');
        $types = [
            $type1->reveal(),
            $type2->reveal(),
        ];
        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $staticFormulation->getKey()->shouldBeCalled()->willReturn($key);
        $staticFormulation->getId()->shouldBeCalled()->willReturn($id);
        $staticFormulation->getTypes()->shouldBeCalled()->willReturn($types);
        $staticFormulation->getTitle('fr')->shouldBeCalled()->willReturn('title');

        $query = new CustomizedStaticFormulationViewQuery($staticFormulation->reveal(), 'fr');
        $handler = new CustomizedStaticFormulationViewQueryHandler();

        $result = $handler->handle($query);
        $expected = new CustomizedStaticFormulationView(
            $key,
            $id,
            'title',
            [
                11 => 'Type 1',
                12 => 'Type 2',
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
