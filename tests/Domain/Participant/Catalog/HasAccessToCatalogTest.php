<?php

namespace Proximum\Vimeet\Tests\Domain\Participant\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Participant\Catalog\HasAccessToCatalog;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class HasAccessToCatalogTest extends TestCase
{
    public function testIsSatisfiedBy()
    {
        $event = $this->prophesize(Event::class);
        $who = $this->prophesize(WhoInterface::class);
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
        
        $hasAccessToCatalog = new HasAccessToCatalog($ruleRepository->reveal());
        $this->assertTrue($hasAccessToCatalog->isSatisfiedBy($sheet->reveal()));
    }
    
    public function testIsNotSatisfiedBy()
    {
        $event = $this->prophesize(Event::class);
        $who = $this->prophesize(WhoInterface::class);
        
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
        $hasAccessToCatalog = new HasAccessToCatalog($ruleRepository->reveal());
        $this->assertFalse($hasAccessToCatalog->isSatisfiedBy($sheet->reveal()));
    }
}
