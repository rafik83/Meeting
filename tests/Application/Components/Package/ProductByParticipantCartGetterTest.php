<?php

namespace Proximum\Vimeet\Tests\Application\Components\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantCartGetter;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProductByParticipantCartGetterTest extends TestCase
{
    public function testGetFromCartWithSeveralProductsAndNoAssignedProduct()
    {
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal(), $product2->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => null, 2000 => null];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetFromCartWithOneProduct()
    {
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal(), $product2->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product1->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => $product1->reveal(), 2000 => null];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetFromCartWithSeveralProducts()
    {
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal(), $product2->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product1->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => $product1->reveal(), 2000 => null];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetFromCartWithAssignedProductToParticipant()
    {
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal(), $product2->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn($product2->reveal());

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product1->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => $product1->reveal(), 2000 => $product2->reveal()];

        $this->assertEquals($expectedResult, $result);
    }

    public function testProductInCartButNotInPackage()
    {
        $productInCart = $this->prophesize(Product::class);
        $productInPackage = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$productInPackage->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($productInCart->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn([$participant1->reveal()]);

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => null];

        $this->assertEquals($expectedResult, $result);
    }
}
