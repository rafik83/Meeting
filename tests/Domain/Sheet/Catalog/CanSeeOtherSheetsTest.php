<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Catalog\CanSeeOtherSheets;

class CanSeeOtherSheetsTest extends TestCase
{
    public function testIsSatisfiedBy()
    {
        $event = $this->prophesize(Event::class);
        $who = $this->prophesize(Type::class);
        $rule = $this->prophesize(Rule::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()
            ->shouldBeCalled()
            ->willReturn($event->reveal());
        ;
        $sheet->getType()
            ->shouldBeCalled()
            ->willReturn($who->reveal());
        ;

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->getByEventAndSeer($event->reveal(), $who->reveal())
            ->shouldBeCalled()
            ->willReturn($rule->reveal());

        $hasAccessToCatalog = new CanSeeOtherSheets($ruleRepository->reveal());
        $this->assertTrue($hasAccessToCatalog->isSatisfiedBy($sheet->reveal()));
    }

    public function testIsNotSatisfiedBy()
    {
        $event = $this->prophesize(Event::class);
        $who = $this->prophesize(Type::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()
            ->shouldBeCalled()
            ->willReturn($event->reveal());
        ;
        $sheet->getType()
            ->shouldBeCalled()
            ->willReturn($who->reveal());
        ;

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->getByEventAndSeer($event->reveal(), $who->reveal())
            ->shouldBeCalled()
            ->willReturn(null);
        $hasAccessToCatalog = new CanSeeOtherSheets($ruleRepository->reveal());
        $this->assertFalse($hasAccessToCatalog->isSatisfiedBy($sheet->reveal()));
    }
}
