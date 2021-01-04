<?php

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\IncludedProductGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IncludedProductGuesserTest extends TestCase
{
    public function testGetIncludedProductIds()
    {
        $event = EventFactory::createEvent();
        $sheet = SheetFactory::create($event);
        ParticipantFactory::create($sheet);

        $cartManager = $this->prophesize(CartManager::class);

        $orderMerger = new Merger();

        $plan = Product::createPlan($event, 'My plan', '', 99, 20, 0, 0);
        $product = $this->prophesize(Product::class);
        $product->getId()->shouldbeCalled()->willReturn(5);
        $product->getData()->shouldbeCalled()->willReturn([]);

        $plan->includeProduct($product->reveal(), 2);

        $planCartRow = new CartRow($sheet, $plan, 1);
        $cart        = new Cart($sheet, [$planCartRow], []);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $ids = (new IncludedProductGuesser($cartManager->reveal(), $orderMerger))->getIncludedProductIds($sheet);

        $this->assertTrue(in_array(5, $ids));
    }
}
