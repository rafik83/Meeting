<?php

namespace Proximum\Vimeet\Tests\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStock;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStockHandler;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class DecrementStockHandlerTest extends TestCase
{
    /** @var ObjectProphecy|PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var DecrementStockHandler */
    private $decrementStockHandler;

    public function setUp()
    {
        $this->promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $this->decrementStockHandler = new DecrementStockHandler($this->promotionCodeRepository->reveal());
    }

    public function testStockIsNull()
    {
        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCode->getStock()->shouldBeCalled()->willReturn(null);

        $this->promotionCodeRepository->set(Argument::any())->shouldNotBeCalled();
        $this->decrementStockHandler->handle(new DecrementStock($promotionCode->reveal()));
    }

    public function testStockIsZero()
    {
        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCode->getStock()->shouldBeCalled()->willReturn(0);

        $this->promotionCodeRepository->set(Argument::any())->shouldNotBeCalled();
        $this->decrementStockHandler->handle(new DecrementStock($promotionCode->reveal()));
    }

    public function testStockIsDecremented()
    {
        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCode->getStock()->shouldBeCalled()->willReturn(2);
        $promotionCode->setStock(1)->shouldBeCalled();

        $this->promotionCodeRepository->set($promotionCode->reveal())->shouldBeCalled();
        $this->decrementStockHandler->handle(new DecrementStock($promotionCode->reveal()));
    }
}
