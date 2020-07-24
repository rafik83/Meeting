<?php

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;

class QuantityMaxGuesserTest extends TestCase
{
    private $cartManager;
    private $merger;
    private $quantityMaxGuesser;
    private $sheet;
    private $package;

    public function setUp(): void
    {
        $this->package = $this->prophesize(Package::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getId()->willReturn(1);
        $this->sheet->getPackage()->willReturn($this->package->reveal());

        $this->cartManager = $this->prophesize(CartManager::class);
        $this->merger = $this->prophesize(Merger::class);

        $this->quantityMaxGuesser = new QuantityMaxGuesser($this->cartManager->reveal(), $this->merger->reveal());
    }

    public function testGetMaxPlanningWithSelectedPlanAndIncludedPlanning(): void
    {
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $includedPlanningProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedPlanningProduct->getQuantity()->shouldBeCalled()->willReturn(1);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn($includedPlanningProduct->reveal());

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(2, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithoutIncludedPlanningLimitedByProductAvailability(): void
    {
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(2);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(2, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithNoCartNeitherOrder(): void
    {
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(3, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithSelectedPlanAndIncludedPlanningInOrder(): void
    {
        $includedPlanningProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedPlanningProduct->getQuantity()->shouldBeCalled()->willReturn(1);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn($includedPlanningProduct->reveal());

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $mergedOrder = $this->prophesize(Order::class);
        $mergedOrder->getPlan()->shouldBeCalled()->willReturn($plan->reveal());
        $mergedOrder->getRowForProduct($planning->reveal())->shouldBeCalled()->willReturn(null);

        $order = $this->prophesize(Order::class);

        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$order->reveal()]);
        $this->merger->merge([$order->reveal()])->shouldBeCalled()->willReturn($mergedOrder->reveal());

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(2, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithPreviousQuantityInOrder(): void
    {
        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn(null);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(INF);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $row = $this->prophesize(Order\Row::class);
        $row->getQuantity()->shouldBeCalled()->willReturn(2);

        $mergedOrder = $this->prophesize(Order::class);
        $mergedOrder->getPlan()->shouldBeCalled()->willReturn($plan->reveal());
        $mergedOrder->getRowForProduct($planning->reveal())->shouldBeCalled()->willReturn($row->reveal());

        $order = $this->prophesize(Order::class);

        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$order->reveal()]);
        $this->merger->merge([$order->reveal()])->shouldBeCalled()->willReturn($mergedOrder->reveal());

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(6, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxByProductWithSelectedPlanAndIncludedProduct(): void
    {
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(false);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $product->getAvailability()->shouldBeCalled()->willReturn(4);

        $includedProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedProduct->getQuantity()->shouldBeCalled()->willReturn(1);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedProduct($product->reveal())->shouldBeCalled()->willReturn($includedProduct->reveal());

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(2, $this->quantityMaxGuesser->getMaxByProduct($this->sheet->reveal(), $product->reveal()));
    }

    public function testGetMaxByProductForAttributableProduct(): void
    {
        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(2);

        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(true);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $product->getAvailability()->shouldBeCalled()->willReturn(4);

        self::assertEquals(
            2,
            $this->quantityMaxGuesser->getMaxByProduct(
                $this->sheet->reveal(),
                $product->reveal()
            )
        );
    }

    public function testGetMaxByProductWithoutIncludedProductLimitedByProductAvailability(): void
    {
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(false);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $product->getAvailability()->shouldBeCalled()->willReturn(2);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedProduct($product->reveal())->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(2, $this->quantityMaxGuesser->getMaxByProduct($this->sheet->reveal(), $product->reveal()));
    }

    public function testGetMaxByProductWithoutIncludedProductLimitedByProductQuantityMax(): void
    {
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(false);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $product->getAvailability()->shouldBeCalled()->willReturn(4);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedProduct($product->reveal())->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(
            3,
            $this->quantityMaxGuesser->getMaxByProduct(
                $this->sheet->reveal(),
                $product->reveal()
            )
        );
    }

    public function testGetMaxByProductThereIsNoCartNeitherOrder(): void
    {
        $cart = $this->prophesize(Cart::class);

        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(false);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(INF);
        $product->getAvailability()->shouldBeCalled()->willReturn(INF);

        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(
            INF,
            $this->quantityMaxGuesser->getMaxByProduct(
                $this->sheet->reveal(),
                $product->reveal()
            )
        );
    }

    public function testGetMaxByProductWithSelectedPlanAndIncludedProductInOrder(): void
    {
        $includedProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedProduct->getQuantity()->shouldBeCalled()->willReturn(1);

        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(false);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(5);
        $product->getAvailability()->shouldBeCalled()->willReturn(6);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedProduct($product->reveal())->shouldBeCalled()->willReturn($includedProduct->reveal());

        $mergedOrder = $this->prophesize(Order::class);
        $mergedOrder->getPlan()->shouldBeCalled()->willReturn($plan->reveal());
        $mergedOrder->getRowForProduct($product->reveal())->shouldBeCalled()->willReturn(null);

        $order = $this->prophesize(Order::class);
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$order->reveal()]);
        $this->merger->merge([$order->reveal()])->shouldBeCalled()->willReturn($mergedOrder->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(
            4,
            $this->quantityMaxGuesser->getMaxByProduct(
                $this->sheet->reveal(),
                $product->reveal()
            )
        );
    }

    public function testGetMaxByProductWithPreviousQuantityInOrder(): void
    {
        $product = $this->prophesize(Product::class);
        $product->isAttributable()->shouldBeCalled()->willReturn(false);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(INF);
        $product->getAvailability()->shouldBeCalled()->willReturn(2);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedProduct($product->reveal())->shouldBeCalled()->willReturn(null);

        $row = $this->prophesize(Order\Row::class);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $mergedOrder = $this->prophesize(Order::class);
        $mergedOrder->getPlan()->shouldBeCalled()->willReturn($plan->reveal());
        $mergedOrder->getRowForProduct($product->reveal())->shouldBeCalled()->willReturn($row->reveal());

        $order = $this->prophesize(Order::class);
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$order->reveal()]);
        $this->merger->merge([$order->reveal()])->shouldBeCalled()->willReturn($mergedOrder->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        self::assertEquals(
            3,
            $this->quantityMaxGuesser->getMaxByProduct(
                $this->sheet->reveal(),
                $product->reveal()
            )
        );
    }
}
