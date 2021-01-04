<?php

namespace Proximum\Vimeet\Tests\Domain\Template\TemplateObject;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Product\IncludedProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\BuyableIncludedProductGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class BuyableIncludedProductGuesserTest extends TestCase
{
    public function testHasBuyableIncludedProduct()
    {
        $key             = 'azerzesq';
        $sheet           = SheetFactory::create();
        $templateObject  = new TemplateObject($key, 'image', ['label' => 'testLabel'], null, null);
        $event           = EventFactory::createEvent();
        $product1        = ProductFactory::create($event);
        $product2        = ProductFactory::create($event);
        $buyableProducts = [$product1, $product2];

        $templateObject->setBuyableProducts($buyableProducts);
        $templateObject->setSheet($sheet);

        $includedProductGuesser = $this->prophesize(IncludedProductGuesser::class);

        $includedProductGuesser->getIncludedProductIds($templateObject->getSheet())->shouldBeCalled()->willReturn([2]);

        $buyableProductGuesser = new BuyableIncludedProductGuesser($includedProductGuesser->reveal());

        $falseResultExpected = $buyableProductGuesser->hasBuyableIncludedProduct($templateObject);
        $this->assertFalse($falseResultExpected);

        $product1        = $this->prophesize(Product::class);
        $product2        = $this->prophesize(Product::class);
        $product1->getId()->shouldBeCalled()->willReturn(1);
        $product2->getId()->shouldBeCalled()->willReturn(2);

        $buyableProducts = [$product1->reveal(), $product2->reveal()];
        $templateObject->setBuyableProducts($buyableProducts);

        $trueResultExpected = $buyableProductGuesser->hasBuyableIncludedProduct($templateObject);
        $this->assertTrue($trueResultExpected);
    }
}
