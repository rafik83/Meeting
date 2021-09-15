<?php

namespace Proximum\Vimeet\Tests\Domain\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\DiscountCalculator;

class DiscountCalculatorTest extends TestCase
{
    public function testGetDiscountForProduct()
    {
        $sheet = $this->prophesize(Sheet::class);
        $dateTime = new \DateTime();

        $event = $this->prophesize(Event::class);
        $event->getCurrency()->willReturn('EUR');
        $event->getVat()->willReturn(20);
        $sheet->getEvent()->willReturn($event->reveal());
        $product1 = Product::createOption($event->reveal(), 'product1', '', 120, 20, 2, 2, 4, true);
        $product2 = Product::createOption($event->reveal(), 'product2', '', 200, 20, 2, 4, 4, true);
        $product3 = Product::createOption($event->reveal(), 'product3', '', 150, 20, 2, 4, 4, true);
        $product4 = Product::createOption($event->reveal(), 'product4', '', 80, 20, 2, 4, 4, true);
        $promotionCode = new PromotionCode($event->reveal(), 'title', 'ABC', null, null);
        $promotionCode->setPromotion($product1, Promotion::TYPE_FREE, 1, 1);
        $promotionCode->setPromotion($product2, Promotion::TYPE_PERCENT_OFF, 50, null);
        $promotionCode->setPromotion($product3, Promotion::TYPE_VALUE_OFF, 100, null);
        $order = new Order($sheet->reveal(), '', $dateTime);
        $row1 = new Order\Row($order, 1, 20, $product1, null, null, 120, null);
        $row2 = new Order\Row($order, 2, 20, $product2, null, null, 200, null);
        $row3 = new Order\Row($order, 1, 20, $product3, null, null, 150, null);
        $row4 = new Order\Row($order, 2, 20, $product4, null, null, 160, null);
        $promotionCodeRow = new Order\PromotionCode($order, $promotionCode, -100, $product1, 20);
        $order->addRow($row1);
        $order->addRow($row2);
        $order->addRow($row3);
        $order->addRow($row4);
        $order->addPromotionCode($promotionCodeRow);

        $discountCalculator = new DiscountCalculator();
        $result1 = $discountCalculator->getDiscountForProduct($order, $promotionCodeRow, $product1);
        $result2 = $discountCalculator->getDiscountForProduct($order, $promotionCodeRow, $product2);
        $result3 = $discountCalculator->getDiscountForProduct($order, $promotionCodeRow, $product3);
        $result4 = $discountCalculator->getDiscountForProduct($order, $promotionCodeRow, $product4);

        $this->assertEquals(-120, $result1);
        $this->assertEquals(-200, $result2);
        $this->assertEquals(-100, $result3);
        $this->assertEquals(0, $result4);
    }
}
