<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\CanDisplayAnalyticsStat;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class CanDisplayAnalyticsStatTest extends TestCase
{
    public function testCan(): void
    {
        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnSheet()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getType()->willReturn($type->reveal());

        $canDisplayAnalyticsStat = new CanDisplayAnalyticsStat();
        $result = $canDisplayAnalyticsStat->isSatisfiedBy($sheet->reveal());

        self::assertTrue($result);
    }

    public function testCantWhenNotInCatalog(): void
    {
        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnSheet()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(false);
        $sheet->getType()->willReturn($type->reveal());

        $canDisplayAnalyticsStat = new CanDisplayAnalyticsStat();
        $result = $canDisplayAnalyticsStat->isSatisfiedBy($sheet->reveal());

        self::assertFalse($result);
    }

    public function testCantWhenTypeNotAllowed(): void
    {
        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnSheet()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getType()->willReturn($type->reveal());

        $canDisplayAnalyticsStat = new CanDisplayAnalyticsStat();
        $result = $canDisplayAnalyticsStat->isSatisfiedBy($sheet->reveal());

        self::assertFalse($result);
    }
}
