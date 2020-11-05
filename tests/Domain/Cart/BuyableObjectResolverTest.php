<?php

namespace Proximum\Vimeet\Tests\Domain\Cart;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;
use Prophecy\Prophecy\ObjectProphecy;

class BuyableObjectResolverTest extends TestCase
{
    /** @var ObjectProphecy */
    private $product;

    /** @var ObjectProphecy */
    private $cart;

    /** @var ObjectProphecy */
    private $order;

    /** @var ObjectProphecy */
    private $cartRow;

    /** @var ObjectProphecy */
    private $productTransformer;

    /** @var ObjectProphecy */
    private $templateObject;

    /** @var ObjectProphecy */
    private $cartManager;

    /** @var ObjectProphecy */
    private $templateProductGuesser;

    /** @var ObjectProphecy */
    private $orderMerger;

    public function setup()
    {
        $this->product = $this->prophesize(Product::class);
        $this->cart = $this->prophesize(Cart::class);
        $this->order = $this->prophesize(Order::class);
        $this->cartRow = $this->prophesize(CartRow::class);
        $this->productTransformer = $this->prophesize(IdToProductTransformer::class);
        $this->templateObject = $this->prophesize(TemplateObject::class);
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $this->orderMerger = $this->prophesize(Merger::class);
    }

    public function testNoOrderRowAndNoCartRow()
    {
        $this->templateObject->getSelectedProduct()->shouldBeCalled()->willReturn(1);
        $this->product->getId()->willReturn(1);
        $this->productTransformer->transform(1)->shouldBeCalled()->willReturn($this->product->reveal());

        $this->cart->getPlanRow()->shouldBeCalled()->willReturn(null);

        $this->cart->getCartRowForProduct($this->product->reveal())->shouldBeCalled()->willReturn(null);

        $this->cart->setProduct($this->product->reveal(), 1)->shouldBeCalled();

        $resolver = new BuyableObjectResolver(
            $this->cartManager->reveal(),
            $this->productTransformer->reveal(),
            $this->templateProductGuesser->reveal(),
            $this->orderMerger->reveal()
        );

        $resolver->addPayableProduct($this->templateObject->reveal(), $this->cart->reveal(), null);
    }

    public function testCartWithThisProductAndNoOrderRow()
    {
        $this->templateObject->getSelectedProduct()->shouldBeCalled()->willReturn(1);
        $this->product->getId()->willReturn(1);
        $this->productTransformer->transform(1)->shouldBeCalled()->willReturn($this->product->reveal());

        $this->cart->getPlanRow()->shouldBeCalled()->willReturn(null);

        $this->cart->getCartRowForProduct($this->product->reveal())->shouldBeCalled()->willReturn($this->cartRow->reveal());

        $this->cartRow->getQuantity()->shouldBeCalled()->willReturn(1);

        $resolver = new BuyableObjectResolver(
            $this->cartManager->reveal(),
            $this->productTransformer->reveal(),
            $this->templateProductGuesser->reveal(),
            $this->orderMerger->reveal()
        );

        $resolver->addPayableProduct($this->templateObject->reveal(), $this->cart->reveal(), null);
    }

    public function testCartWithThisProductAndOrderWithThisProduct()
    {
        $this->templateObject->getSelectedProduct()->shouldBeCalled()->willReturn(1);
        $this->product->getId()->willReturn(1);
        $this->productTransformer->transform(1)->shouldBeCalled()->willReturn($this->product->reveal());

        $this->order->getPlan()->shouldBeCalled()->willReturn($this->product->reveal());
        $this->product->getIncludedProduct($this->product->reveal())->shouldBeCalled()->willReturn(null);

        $this->cart->getOrderCartQuantity($this->product->reveal(), $this->order->reveal())->shouldBeCalled()->willReturn(1);

        $resolver = new BuyableObjectResolver(
            $this->cartManager->reveal(),
            $this->productTransformer->reveal(),
            $this->templateProductGuesser->reveal(),
            $this->orderMerger->reveal()
        );

        $resolver->addPayableProduct($this->templateObject->reveal(), $this->cart->reveal(), $this->order->reveal());
    }

    public function testNoCartRowAndOrderWithThisProduct()
    {
        $this->templateObject->getSelectedProduct()->shouldBeCalled()->willReturn(1);
        $this->product->getId()->willReturn(1);
        $this->productTransformer->transform(1)->shouldBeCalled()->willReturn($this->product->reveal());

        $this->order->getPlan()->shouldBeCalled()->willReturn($this->product->reveal());
        $this->product->getIncludedProduct($this->product->reveal())->shouldBeCalled()->willReturn(null);

        $this->cart->getOrderCartQuantity($this->product->reveal(), $this->order->reveal())->shouldBeCalled()->willReturn(1);

        $resolver = new BuyableObjectResolver(
            $this->cartManager->reveal(),
            $this->productTransformer->reveal(),
            $this->templateProductGuesser->reveal(),
            $this->orderMerger->reveal()
        );

        $resolver->addPayableProduct($this->templateObject->reveal(), $this->cart->reveal(), $this->order->reveal());
    }

    public function testCartQuantityToZeroAndNoOrderRow()
    {
        $this->templateObject->getSelectedProduct()->shouldBeCalled()->willReturn(1);
        $this->product->getId()->willReturn(1);
        $this->productTransformer->transform(1)->shouldBeCalled()->willReturn($this->product->reveal());

        $this->cart->getPlanRow()->shouldBeCalled()->willReturn(null);

        $this->cart->getCartRowForProduct($this->product->reveal())->shouldBeCalled()->willReturn($this->cartRow->reveal());

        $this->cartRow->getQuantity()->shouldBeCalled()->willReturn(0);
        $this->cartRow->setQuantity(1)->shouldBeCalled();

        $resolver = new BuyableObjectResolver(
            $this->cartManager->reveal(),
            $this->productTransformer->reveal(),
            $this->templateProductGuesser->reveal(),
            $this->orderMerger->reveal()
        );

        $resolver->addPayableProduct($this->templateObject->reveal(), $this->cart->reveal(), null);
    }
}
