<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Order\RemovePromotionCode;
use Proximum\Vimeet\Application\Command\Order\RemovePromotionCodeHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Order\PromotionCodeRepositoryInterface;

class RemovePromotionCodeHandlerTest extends TestCase
{
    /** @var RemovePromotionCodeHandler */
    private $removePromotionCodeHandler;

    /** @var ObjectProphecy|PromotionCodeRepositoryInterface */
    private $orderPromotionCodeRepository;

    /** @var ObjectProphecy|SheetIndexerInterface */
    private $sheetIndexer;

    /** @var ObjectProphecy|Order */
    private $order;

    /** @var ObjectProphecy|PromotionCode */
    private $promotionCode;

    /** @var Order\PromotionCode */
    private $orderPromotionCode;

    /** @var ObjectProphecy|Product */
    private $product;

    public function setUp()
    {
        $this->product = $this->prophesize(Product::class);
        $this->order = $this->prophesize(Order::class);
        $this->promotionCode = $this->prophesize(PromotionCode::class);
        $this->orderPromotionCode = new Order\PromotionCode(
            $this->order->reveal(),
            $this->promotionCode->reveal(),
            99,
            $this->product->reveal(),
            20
        );
        $this->sheetIndexer = $this->prophesize(SheetIndexerInterface::class);
        $this->orderPromotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $this->removePromotionCodeHandler = new RemovePromotionCodeHandler(
            $this->orderPromotionCodeRepository->reveal(),
            $this->sheetIndexer->reveal()
        );
    }

    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $this->order->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $this->order->getPromotionCodes()->shouldBeCalled()->willReturn([$this->orderPromotionCode]);
        $this->order->removePromotionCode($this->orderPromotionCode)->shouldBeCalled();
        $this->orderPromotionCodeRepository->remove($this->orderPromotionCode)->shouldBeCalled();
        $this->sheetIndexer->updateSheets([$sheet->reveal()])->shouldBeCalled();

        $this->removePromotionCodeHandler->handle(
            new RemovePromotionCode($this->orderPromotionCode, $this->order->reveal())
        );
    }

    public function testInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->order->getPromotionCodes()->shouldBeCalled()->willReturn([]);
        $this->order->removePromotionCode($this->orderPromotionCode)->shouldNotBeCalled();
        $this->orderPromotionCodeRepository->remove($this->orderPromotionCode)->shouldNotBeCalled();
        $this->sheetIndexer->updateSheets(Argument::any())->shouldNotBeCalled();

        $this->removePromotionCodeHandler->handle(
            new RemovePromotionCode($this->orderPromotionCode, $this->order->reveal())
        );
    }
}
