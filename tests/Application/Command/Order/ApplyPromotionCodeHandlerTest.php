<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Order\ApplyPromotionCode;
use Proximum\Vimeet\Application\Command\Order\ApplyPromotionCodeHandler;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStock;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStockHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNegativeRowException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotUsedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class ApplyPromotionCodeHandlerTest extends TestCase
{
    /** @var ObjectProphecy|Merger */
    private $orderMerger;

    /** @var \DateTime */
    private $dateTime;

    /** @var ApplyPromotionCodeHandler */
    private $applyPromotionCodeHandler;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var Order */
    private $order;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|OrderRepositoryInterface */
    private $orderRepository;

    /** @var ObjectProphecy|DecrementStockHandler */
    private $decrementStockHandler;

    /** @var ObjectProphecy|SheetIndexerInterface */
    private $sheetIndexer;

    public function setUp()
    {
        $this->orderMerger = $this->prophesize(Merger::class);
        $this->dateTime = new \DateTime('2019-05-20');

        $this->event = $this->prophesize(Event::class);
        $this->event->getCurrency()->willReturn('EUR');
        $this->event->getVat()->willReturn(0.2);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());

        $this->order = new Order($this->sheet->reveal(), '', new \DateTime('2019-05-15'));

        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->decrementStockHandler = $this->prophesize(DecrementStockHandler::class);
        $this->sheetIndexer = $this->prophesize(SheetIndexerInterface::class);

        $this->applyPromotionCodeHandler = new ApplyPromotionCodeHandler(
            $this->orderMerger->reveal(),
            $this->orderRepository->reveal(),
            $this->decrementStockHandler->reveal(),
            $this->sheetIndexer->reveal(),
            $this->dateTime
        );
    }

    public function testPromotionCodeNotFoundException()
    {
        $this->expectException(PromotionCodeNotFoundException::class);
        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testPromotionCodeOutDatedException()
    {
        $this->expectException(PromotionCodeOutDatedException::class);
        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = new PromotionCode(
            $this->event->reveal(),
            'Promotion code -10%',
            'ABCDEF',
            null,
            new \DateTime('2019-04-01')
        );
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testPromotionCodeSoldOutException()
    {
        $this->expectException(PromotionCodeSoldOutException::class);
        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = new PromotionCode(
            $this->event->reveal(),
            'Promotion code -10%',
            'ABCDEF',
            0
        );
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testPromotionCodeNotUsedException()
    {
        $this->expectException(PromotionCodeNotUsedException::class);
        $promotionCode = new PromotionCode($this->event->reveal(), 'Promotion code -10%', 'ABCDEF');
        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = $promotionCode;
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testPromotionCodeConflictException()
    {
        $this->expectException(PromotionCodeConflictException::class);

        $product = $this->prophesize(Product::class);

        $promotionCode1 = new PromotionCode($this->event->reveal(), 'Promotion code -10%', 'ABCDEF');
        $promotionCode1->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 10);
        $this->order->addPromotionCode(
            new Order\PromotionCode($this->order, $promotionCode1, 100, $product->reveal(), 20)
        );
        $this->order->addRow(new Order\Row($this->order, 1, 20, $product->reveal()));

        $promotionCode2 = new PromotionCode($this->event->reveal(), 'Promotion code -30%', 'GHIJKL');
        $promotionCode2->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 10);

        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = $promotionCode2;
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testPromotionCodeNegativeRowException()
    {
        $this->expectException(PromotionCodeNegativeRowException::class);

        $product1 = $this->prophesize(Product::class);
        $this->order->addRow(new Order\Row($this->order, 1, 20, $product1->reveal()));

        $product2 = $this->prophesize(Product::class);
        $this->order->addRow(new Order\Row($this->order, -1, 20, $product2->reveal()));

        $promotionCode1 = new PromotionCode($this->event->reveal(), 'Promotion code -10%', 'ABCDEF');
        $promotionCode1->setPromotion($product1->reveal(), Promotion::TYPE_VALUE_OFF, 10);
        $this->order->addPromotionCode(
            new Order\PromotionCode($this->order, $promotionCode1, 100, $product1->reveal(), 20)
        );

        $promotionCode2 = new PromotionCode($this->event->reveal(), 'Promotion code -30%', 'GHIJKL');
        $promotionCode2->setPromotion($product2->reveal(), Promotion::TYPE_VALUE_OFF, 20);

        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = $promotionCode2;
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testPromotionCodeAlreadyExistException()
    {
        $this->expectException(PromotionCodeAlreadyExistException::class);

        $product = $this->prophesize(Product::class);
        $this->order->addRow(new Order\Row($this->order, 1, 20, $product->reveal()));

        $promotionCode = new PromotionCode($this->event->reveal(), 'Promotion code -10%', 'ABCDEF');
        $promotionCode->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 10);

        $mergedOrder = new Order($this->sheet->reveal(), '', new \DateTime('2019-05-15'));
        $mergedOrder->addPromotionCode(
            new Order\PromotionCode($mergedOrder, $promotionCode, 100, $product->reveal(), 20)
        );

        $this->orderMerger
            ->getMergedOrders($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($mergedOrder)
        ;

        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = $promotionCode;
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);
    }

    public function testHandle()
    {
        $product = $this->prophesize(Product::class);
        $product->getVat()->shouldBeCalled()->willReturn(20);
        $product->getSerializedData()->shouldBeCalled()->willReturn('serializedData');
        $product->getUnitPrice()->shouldBeCalled()->willReturn(99);

        $this->order->addRow(new Order\Row($this->order, 1, 20, $product->reveal()));

        $promotionCode = new PromotionCode($this->event->reveal(), 'Promotion code -10%', 'ABCDEF');
        $promotionCode->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 10);

        $this->orderMerger
            ->getMergedOrders($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->orderRepository->set($this->order)->shouldBeCalled();
        $this->decrementStockHandler->handle(new DecrementStock($promotionCode))->shouldBeCalled();
        $this->sheetIndexer->updateSheets([$this->sheet->reveal()])->shouldBeCalled();

        $applyPromotionCode = new ApplyPromotionCode($this->order);
        $applyPromotionCode->promotionCode = $promotionCode;
        $this->applyPromotionCodeHandler->handle($applyPromotionCode);

        $addedPromotionCode = $this->order->getPromotionCodes()[0];
        $this->assertEquals($product->reveal(), $addedPromotionCode->getProduct());
        $this->assertEquals($promotionCode, $addedPromotionCode->getPromotionCode());
        $this->assertEquals(-10.0, $addedPromotionCode->getPrice());
    }
}
