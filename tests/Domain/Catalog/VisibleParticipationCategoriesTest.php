<?php

namespace Proximum\Vimeet\Tests\Domain\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationCategories;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class VisibleParticipationCategoriesTest extends TestCase
{
    public function testGetAllowedCategoriesList()
    {
        $event     = EventFactory::createEvent();
        $category1 = $this->prophesize(Category::class);
        $category2 = $this->prophesize(Category::class);
        $category3 = $this->prophesize(Category::class);
        $rule1     = $this->prophesize(Rule::class);
        $rule2     = $this->prophesize(Rule::class);
        $sheet     = $this->prophesize(Sheet::class);
        $type      = $this->prophesize(Type::class);

        $type->getCategories()->shouldBeCalled()->willReturn([$category2]);
        $category3->getId()->shouldBeCalled()->willReturn(8);

        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $rule1->getSeerCategory()->shouldBeCalled()->willReturn($category1->reveal());
        $rule2->getSeerCategory()->shouldBeCalled()->willReturn($category2->reveal());
        $rule2->getSeeableCategory()->shouldBeCalled()->willReturn($category3->reveal());

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository
            ->getByEvent($event)
            ->shouldBeCalled()
            ->willReturn([
                $rule1,
                $rule2,
            ]);

        $visibleParticipationCategories = new VisibleParticipationCategories(
            $ruleRepository->reveal()
        );
        $result = $visibleParticipationCategories->getAllowedCategoriesList($sheet->reveal());

        $this->assertEquals($category3->reveal(), $result[8]);
    }
}
