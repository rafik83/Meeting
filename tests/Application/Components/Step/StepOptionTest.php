<?php

namespace Proximum\Vimeet\Tests\Application\Components\Step;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Application\Components\Step\StepOption;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class StepOptionTest extends TestCase
{
    public function testBuild()
    {
        $dateTime = new \DateTime();
        $productAttributedToParticipantRepository = $this->prophesize(ProductAttributedToParticipantRepositoryInterface::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        // product not bought
        $product1 = $this->prophesize(Product::class);
        $product1->getId()->shouldBeCalled()->willReturn(123);
        $product1->isAttributable()->shouldBeCalled()->willReturn(false);

        // product attributable
        $product2 = $this->prophesize(Product::class);
        $product2->getId()->shouldBeCalled()->willReturn(321);
        $product2->isAttributable()->shouldBeCalled()->willReturn(true);
        $productAttributedToParticipantRepository
            ->findByProductAndParticipants($product2->reveal(), $participants)
            ->shouldBeCalled()
            ->willReturn([]);

        // product with 2 elements in Order
        $product3 = $this->prophesize(Product::class);
        $product3->getId()->shouldBeCalled()->willReturn(417);
        $product3->isAttributable()->shouldBeCalled()->willReturn(false);
        $orderRowForProduct3 = $this->prophesize(Order\Row::class);
        $orderRowForProduct3->getQuantity()->shouldBeCalled()->willReturn(2);

        // product with 1 element in Cart row
        $product4 = $this->prophesize(Product::class);
        $product4->getId()->shouldBeCalled()->willReturn(513);
        $product4->isAttributable()->shouldBeCalled()->willReturn(false);
        $cartRowForProduct4 = $this->prophesize(CartRow::class);
        $cartRowForProduct4->getProduct()->shouldBeCalled()->willReturn($product4->reveal());
        $cartRowForProduct4->getQuantity()->shouldBeCalled()->willReturn(3);

        // product with 3 elements in Order and 2 elements in Cart row
        $product5 = $this->prophesize(Product::class);
        $product5->getId()->shouldBeCalled()->willReturn(611);
        $product5->isAttributable()->shouldBeCalled()->willReturn(false);
        $orderRowForProduct5 = $this->prophesize(Order\Row::class);
        $orderRowForProduct5->getQuantity()->shouldBeCalled()->willReturn(3);
        $cartRowForProduct5 = $this->prophesize(CartRow::class);
        $cartRowForProduct5->getProduct()->shouldBeCalled()->willReturn($product5->reveal());
        $cartRowForProduct5->getQuantity()->shouldBeCalled()->willReturn(2);

        // product with 2 elements in cart row
        $product6 = $this->prophesize(Product::class);
        $product6->getId()->shouldBeCalled()->willReturn(711);
        $product6->isAttributable()->shouldBeCalled()->willReturn(true);
        $cartRowForProduct6 = $this->prophesize(CartRow::class);
        $cartRowForProduct6->getProduct()->shouldBeCalled()->willReturn($product6->reveal());
        $cartRowForProduct6->getQuantity()->shouldBeCalled()->willReturn(2);
        $cartRowForProduct6->getParticipants()->shouldBeCalled()->willReturn($participants);

        // product with no cart row but ProductAttributedToParticipant
        $product7 = $this->prophesize(Product::class);
        $product7->getId()->shouldBeCalled()->willReturn(811);
        $product7->isAttributable()->shouldBeCalled()->willReturn(true);
        $productAttributedToParticipant = $this->prophesize(ProductAttributedToParticipant::class);
        $productAttributedToParticipant->getParticipant()->shouldBeCalled()->willReturn($participant1->reveal());
        $productAttributedToParticipantRepository
            ->findByProductAndParticipants($product7->reveal(), $participants)
            ->shouldBeCalled()
            ->willReturn([$productAttributedToParticipant->reveal()]);
        $orderRowForProduct7 = $this->prophesize(Order\Row::class);
        $orderRowForProduct7->getQuantity()->shouldBeCalled()->willReturn(1);

        $cart = $this->prophesize(Cart::class);
        $cart->getOptionsRowArray()->willReturn(
            [
                $cartRowForProduct4->reveal(),
                $cartRowForProduct5->reveal(),
                $cartRowForProduct6->reveal(),
            ]
        );

        $order = $this->prophesize(Order::class);
        $order->getRowForProduct($product1->reveal())->willReturn(null);
        $order->getRowForProduct($product2->reveal())->willReturn(null);
        $order->getRowForProduct($product3->reveal())->willReturn($orderRowForProduct3->reveal());
        $order->getRowForProduct($product4->reveal())->willReturn(null);
        $order->getRowForProduct($product5->reveal())->willReturn($orderRowForProduct5->reveal());
        $order->getRowForProduct($product6->reveal())->willReturn(null);
        $order->getRowForProduct($product7->reveal())->willReturn($orderRowForProduct7->reveal());

        $package = $this->prophesize(Package::class);
        $package
            ->getAvailablesOptions($dateTime)
            ->shouldBeCalled()
            ->willReturn(
                [
                    $product1->reveal(),
                    $product2->reveal(),
                    $product3->reveal(),
                    $product4->reveal(),
                    $product5->reveal(),
                    $product6->reveal(),
                    $product7->reveal(),
                ]
            )
        ;

        $sheet = $this->prophesize(Sheet::class);
        $sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$order->reveal()]);
        $sheet->getPackage()->shouldBeCalled()->willReturn($package->reveal());
        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn($participants);

        $orderMerger = $this->prophesize(Merger::class);
        $orderMerger->merge([$order->reveal()])->shouldBeCalled()->willReturn($order->reveal());

        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet->reveal(), 3)->shouldBeCalled()->willReturn($cart->reveal());

        $stepOption = new StepOption(
            $orderMerger->reveal(),
            $cartManager->reveal(),
            $productAttributedToParticipantRepository->reveal(),
            $dateTime
        );
        $result = $stepOption->build($sheet->reveal(), 3);

        $expectedSelectOptions = new SelectOptions($sheet->reveal(), 3);
        $expectedSelectOptions->options = [
            123 => new OptionRow(
                0,
                [],
                false
            ),
            321 => new OptionRow(
                0,
                [],
                true
            ),
            417 => new OptionRow(
                2,
                [],
                false
            ),
            513 => new OptionRow(
                3,
                [],
                false
            ),
            611 => new OptionRow(
                5,
                [],
                false
            ),
            711 => new OptionRow(
                2,
                [$participant1->reveal(), $participant2->reveal()],
                true
            ),
            811 => new OptionRow(
                1,
                [$participant1->reveal()],
                true
            ),
        ];

        $this->assertEquals($expectedSelectOptions, $result);
    }
}
