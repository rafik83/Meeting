<?php

namespace Proximum\Vimeet\Tests\Application\Query\PromotionCode\Batch;

use Proximum\Vimeet\Application\Query\PromotionCode\Batch\CanBeUpdatable;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CanBeUpdatableTest extends TestCase
{
    public function testIsSatisfiableBy()
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $promotionCode1 = $this->prophesize(PromotionCode::class);
        $orderRepository->hasOrderWithPromotionCode($promotionCode1)->shouldBeCalled()->willReturn(false);
        $promotionCode2 = $this->prophesize(PromotionCode::class);
        $orderRepository->hasOrderWithPromotionCode($promotionCode2)->shouldBeCalled()->willReturn(false);

        $promotionCodeGroup1 = $this->prophesize(PromotionCodeGroup::class);
        $promotionCodeGroup1->getPromotionCodes()->shouldBeCalled()->willReturn(
            [$promotionCode1->reveal(), $promotionCode2->reveal()]
        );

        $promotionCode3 = $this->prophesize(PromotionCode::class);
        $orderRepository->hasOrderWithPromotionCode($promotionCode3)->shouldBeCalled()->willReturn(true);
        $promotionCode4 = $this->prophesize(PromotionCode::class);
        $orderRepository->hasOrderWithPromotionCode($promotionCode4)->shouldNotBeCalled();

        $promotionCodeGroup2 = $this->prophesize(PromotionCodeGroup::class);
        $promotionCodeGroup2->getPromotionCodes()->shouldBeCalled()->willReturn(
            [$promotionCode3->reveal(), $promotionCode4->reveal()]
        );

        $canBeUpdatable = new CanBeUpdatable($orderRepository->reveal());

        $this->assertTrue($canBeUpdatable->isSatisfiableBy($promotionCodeGroup1->reveal()));
        $this->assertFalse($canBeUpdatable->isSatisfiableBy($promotionCodeGroup2->reveal()));
    }
}
