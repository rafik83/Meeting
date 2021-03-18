<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\CanDisplayAnalyticsViewLink;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class CanDisplayAnalyticsViewLinkTest extends TestCase
{
    public function testCanDisplayAnalyticsViewLink(): void
    {
        $analytics = $this->prophesize(Sheet\Analytics::class);
        $analytics->getViews()->willReturn(1);

        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(true);
        $type->canDisplayAnalyticsOnSheet()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getAnalytics()->willReturn($analytics->reveal());

        $canDisplayAnalyticsViewLink = new CanDisplayAnalyticsViewLink();
        $result = $canDisplayAnalyticsViewLink->isSatisfiedBy($sheet->reveal());

        self::assertTrue($result);
    }

    public function testNotInCatalog(): void
    {
        $analytics = $this->prophesize(Sheet\Analytics::class);
        $analytics->getViews()->willReturn(1);

        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(true);
        $type->canDisplayAnalyticsOnSheet()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->isInInternalCatalog()->willReturn(false);
        $sheet->getAnalytics()->willReturn($analytics->reveal());

        $canDisplayAnalyticsViewLink = new CanDisplayAnalyticsViewLink();
        $result = $canDisplayAnalyticsViewLink->isSatisfiedBy($sheet->reveal());

        self::assertFalse($result);
    }

    public function testNoAnalyticsOnSheet(): void
    {
        $analytics = $this->prophesize(Sheet\Analytics::class);
        $analytics->getViews()->willReturn(1);

        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(true);
        $type->canDisplayAnalyticsOnSheet()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getAnalytics()->willReturn($analytics->reveal());

        $canDisplayAnalyticsViewLink = new CanDisplayAnalyticsViewLink();
        $result = $canDisplayAnalyticsViewLink->isSatisfiedBy($sheet->reveal());

        self::assertFalse($result);
    }

    public function testNoAnalyticsOnCatalog(): void
    {
        $analytics = $this->prophesize(Sheet\Analytics::class);
        $analytics->getViews()->willReturn(1);

        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(false);
        $type->canDisplayAnalyticsOnSheet()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getAnalytics()->willReturn($analytics->reveal());

        $canDisplayAnalyticsViewLink = new CanDisplayAnalyticsViewLink();
        $result = $canDisplayAnalyticsViewLink->isSatisfiedBy($sheet->reveal());

        self::assertFalse($result);
    }

    public function testNoViews(): void
    {
        $analytics = $this->prophesize(Sheet\Analytics::class);
        $analytics->getViews()->willReturn(0);

        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(true);
        $type->canDisplayAnalyticsOnSheet()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getAnalytics()->willReturn($analytics->reveal());

        $canDisplayAnalyticsViewLink = new CanDisplayAnalyticsViewLink();
        $result = $canDisplayAnalyticsViewLink->isSatisfiedBy($sheet->reveal());

        self::assertFalse($result);
    }
}
