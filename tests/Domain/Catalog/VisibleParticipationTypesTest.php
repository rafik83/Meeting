<?php

namespace Proximum\Vimeet\Tests\Domain\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationTypesTest extends TestCase
{
    public function testGetAllowedTypesList()
    {
        // Context
        $sheetType = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $rule1 = $this->prophesize(Rule::class);
        $rule2 = $this->prophesize(Rule::class);
        $rule3 = $this->prophesize(Rule::class);
        $rule4 = $this->prophesize(Rule::class);
        $otherType = $this->prophesize(Type::class);
        $anotherTypeNotSeeable = $this->prophesize(Type::class);
        $category1 = $this->prophesize(Category::class);
        $category2 = $this->prophesize(Category::class);
        $sheetType->getId()->willReturn(1337);
        $otherType->getId()->willReturn(7331);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getType()->willReturn($sheetType->reveal());
        $category1->getTypes()->willReturn([$otherType->reveal(), $anotherTypeNotSeeable->reveal()]);
        $category2->getTypes()->willReturn([$otherType->reveal(), $sheetType->reveal()]);

        // Expected
        $rule1->getSeerType()->willReturn($sheetType->reveal());
        $rule2->getSeerType()->willReturn($otherType);
        $rule2->getSeerCategory()->willReturn(null);
        $rule3->getSeerType()->willReturn(null);
        $rule3->getSeerCategory()->willReturn($category1->reveal());
        $rule4->getSeerType()->willReturn(null);
        $rule4->getSeerCategory()->willReturn($category2->reveal());

        $rule1->getSeeableCategory()->shouldBeCalled()->willReturn(null);
        $rule1->getSeeableType()->shouldBeCalled()->willReturn($otherType->reveal());
        $rule2->getSeeableCategory()->shouldNotBeCalled();
        $rule2->getSeeableType()->shouldNotBeCalled();
        $rule3->getSeeableCategory()->shouldNotBeCalled();
        $rule3->getSeeableType()->shouldNotBeCalled();
        $rule4->getSeeableCategory()->shouldBeCalled()->willReturn($category2->reveal());
        $rule4->getSeeableType()->shouldNotBeCalled();

        // Mock
        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository
            ->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $rule1,
                $rule2,
                $rule3,
                $rule4,
            ])
        ;

        // VisibleParticipationTypes
        $visibleParticipationTypes = new VisibleParticipationTypes($ruleRepository->reveal());
        $result = $visibleParticipationTypes->getAllowedTypesList($sheet->reveal());

        $expected = [
            1337 => $sheetType->reveal(),
            7331 => $otherType->reveal(),
        ];

        $this->assertEquals($expected, $result);
    }
}
