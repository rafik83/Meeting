<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class PromotionTest extends TestCase
{
    public function testGetDiscountAmountForProduct()
    {
        $promotionCode = $this->prophesize(PromotionCode::class);

        $product1 = $this->prophesize(Product::class);
        $product1->getUnitPrice()->willReturn(99);

        $promotion1 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_FREE, 0);
        $this->assertEquals(-99, $promotion1->getDiscountAmountForProduct($product1->reveal(), 1));
        $this->assertEquals(-198, $promotion1->getDiscountAmountForProduct($product1->reveal(), 2));

        $promotion2 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_FREE, 0, 1);
        $this->assertEquals(-99, $promotion2->getDiscountAmountForProduct($product1->reveal(), 1));
        $this->assertEquals(-99, $promotion2->getDiscountAmountForProduct($product1->reveal(), 2));

        $promotion3 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_PERCENT_OFF, 50);
        $this->assertEquals(-49.5, $promotion3->getDiscountAmountForProduct($product1->reveal(), 1));
        $this->assertEquals(-99, $promotion3->getDiscountAmountForProduct($product1->reveal(), 2));

        $promotion4 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_PERCENT_OFF, 50, 1);
        $this->assertEquals(-49.5, $promotion4->getDiscountAmountForProduct($product1->reveal(), 1));
        $this->assertEquals(-49.5, $promotion4->getDiscountAmountForProduct($product1->reveal(), 2));

        $promotion5 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_VALUE_OFF, 50);
        $this->assertEquals(-50, $promotion5->getDiscountAmountForProduct($product1->reveal(), 1));
        $this->assertEquals(-50, $promotion5->getDiscountAmountForProduct($product1->reveal(), 2));

        $promotion6 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_VALUE_OFF, 60, 1);
        $this->assertEquals(-60, $promotion6->getDiscountAmountForProduct($product1->reveal(), 1));
        $this->assertEquals(-60, $promotion6->getDiscountAmountForProduct($product1->reveal(), 2));

        $promotion7 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_FREE, 0);
        $this->assertEquals(0, $promotion7->getDiscountAmountForProduct($product1->reveal(), -1));

        $anotherProduct = $this->prophesize(Product::class);
        $promotion8 = new Promotion($promotionCode->reveal(), $product1->reveal(), Promotion::TYPE_FREE, 0);
        $this->assertEquals(0, $promotion8->getDiscountAmountForProduct($anotherProduct->reveal(), 1));
    }
}
