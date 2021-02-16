<?php

namespace Proximum\Vimeet\Tests\Application\Components\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantCartGetter;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantGetter;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProductByParticipantGetterTest extends TestCase
{
    public function testProductIndexedByParticipantIdNotChangedHandle()
    {
        $product1 = $this->prophesize(Product::class);
        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(111);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());
        $sheet->getParticipantsArray()->willReturn([$participant1->reveal()]);

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->willReturn($sheet->reveal());

        $productByParticipantCartGetter = $this->prophesize(ProductByParticipantCartGetter::class);
        $productByParticipantCartGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn([
                111 => $product1->reveal(),
            ])
        ;

        $participantProductViewQueryHandler = $this->prophesize(ParticipantProductViewQueryHandler::class);
        $participantProductViewQueryHandler
            ->handle(new ParticipantProductViewQuery($sheet->reveal()))
            ->shouldNotBeCalled()
        ;

        $productByParticipantGetter = new ProductByParticipantGetter(
            $productByParticipantCartGetter->reveal(),
            $participantProductViewQueryHandler->reveal()
        );

        $this->assertEquals(
            [
                111 => $product1->reveal(),
            ],
            $productByParticipantGetter->handle($cart->reveal())
        );
    }

    public function testThereAreNotAssignedProductToParticipantHandle()
    {
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal(), $product2->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(111);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(222);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());
        $sheet->getParticipantsArray()->willReturn([$participant1->reveal(), $participant2->reveal()]);

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->willReturn($sheet->reveal());

        $productByParticipantCartGetter = $this->prophesize(ProductByParticipantCartGetter::class);
        $productByParticipantCartGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn([
                111 => $product1->reveal(),
            ])
        ;

        $participantProductViewQueryHandler = $this->prophesize(ParticipantProductViewQueryHandler::class);
        $participantProductViewQueryHandler
            ->handle(new ParticipantProductViewQuery($sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $productByParticipantGetter = new ProductByParticipantGetter(
            $productByParticipantCartGetter->reveal(),
            $participantProductViewQueryHandler->reveal()
        );

        $this->assertEquals(
            [
                111 => $product1->reveal(),
                222 => null,
            ],
            $productByParticipantGetter->handle($cart->reveal())
        );
    }

    public function testThereAreOnlyOneProductInPackageHandle()
    {
        $product = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(111);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(222);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());
        $sheet->getParticipantsArray()->willReturn([$participant1->reveal(), $participant2->reveal()]);

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->willReturn($sheet->reveal());

        $productByParticipantCartGetter = $this->prophesize(ProductByParticipantCartGetter::class);
        $productByParticipantCartGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $participantProductViewQueryHandler = $this->prophesize(ParticipantProductViewQueryHandler::class);
        $participantProductViewQueryHandler
            ->handle(new ParticipantProductViewQuery($sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $productByParticipantGetter = new ProductByParticipantGetter(
            $productByParticipantCartGetter->reveal(),
            $participantProductViewQueryHandler->reveal()
        );

        $this->assertEquals(
            [
                111 => $product->reveal(),
                222 => $product->reveal(),
            ],
            $productByParticipantGetter->handle($cart->reveal())
        );
    }

    public function testThereIsARemainingIncludedProduct()
    {
        $product1 = $this->prophesize(Product::class);
        $product1->getId()->willReturn(9);
        $product2 = $this->prophesize(Product::class);
        $product2->getId()->willReturn(10);

        $package = $this->prophesize(Package::class);
        $package->getParticipants()->willReturn([$product1->reveal(), $product2->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(111);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(222);

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getId()->willReturn(333);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());
        $sheet
            ->getParticipantsArray()
            ->willReturn(
                [
                    $participant1->reveal(),
                    $participant2->reveal(),
                    $participant3->reveal(),
                ]
            )
        ;

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->willReturn($sheet->reveal());

        $productByParticipantCartGetter = $this->prophesize(ProductByParticipantCartGetter::class);
        $productByParticipantCartGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn([
                111 => $product1->reveal()
            ])
        ;

        $participantProductViewQueryHandler = $this->prophesize(ParticipantProductViewQueryHandler::class);
        $participantProductViewQueryHandler
            ->handle(new ParticipantProductViewQuery($sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn([
                new ParticipantProductView(
                    9,
                    '',
                    '',
                    '9.99',
                    'EUR',
                    'vatMode',
                    2,
                    0,
                    true,
                    0,
                    false
                ),
                new ParticipantProductView(
                    10,
                    '',
                    '',
                    '8.99',
                    'EUR',
                    'vatMode',
                    2,
                    0,
                    true,
                    1,
                    true
                ),
            ])
        ;

        $productByParticipantGetter = new ProductByParticipantGetter(
            $productByParticipantCartGetter->reveal(),
            $participantProductViewQueryHandler->reveal()
        );

        $this->assertEquals(
            [
                111 => $product1->reveal(),
                222 => $product2->reveal(),
                333 => null,
            ],
            $productByParticipantGetter->handle($cart->reveal())
        );
    }
}
