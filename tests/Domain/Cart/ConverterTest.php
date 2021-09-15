<?php

namespace Proximum\Vimeet\Tests\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStock;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStockHandler;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\Converter;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class ConverterTest extends TestCase
{
    public function testToOrder()
    {
        $dateTime = new \DateTime();

        $event = $this->prophesize(Event::class);
        $event->getCurrency()->willReturn('EUR');
        $event->getVat()->willReturn(0.2);

        $package = $this->prophesize(Package::class);
        $package->serializeData()->shouldBeCalled()->willReturn('Package serialized data');

        $participant1 = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet->getPackage()->shouldBeCalled()->willReturn($package->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->isParticipant()->shouldBeCalled()->willReturn(false);
        $plan->getSerializedData()->shouldBeCalled()->willReturn('Plan serialized data');
        $plan->isAttributable()->shouldBeCalled()->willReturn(false);
        $plan->getVat()->shouldBeCalled()->willReturn(20);
        $plan->getUnitPrice()->shouldBeCalled()->willReturn(999);

        $participantProduct = $this->prophesize(Product::class);
        $participantProduct->isParticipant()->shouldBeCalled()->willReturn(true);
        $participantProduct->getSerializedData()->shouldBeCalled()->willReturn('Participant product serialized data');
        $participantProduct->isAttributable()->shouldBeCalled()->willReturn(false);
        $participantProduct->getVat()->shouldBeCalled()->willReturn(20);
        $participantProduct->getUnitPrice()->shouldBeCalled()->willReturn(123);

        $chair = $this->prophesize(Product::class);
        $chair->isParticipant()->shouldBeCalled()->willReturn(false);
        $chair->getSerializedData()->shouldBeCalled()->willReturn('Chair serialized data');
        $chair->isAttributable()->shouldBeCalled()->willReturn(false);
        $chair->getVat()->shouldBeCalled()->willReturn(20);
        $chair->getUnitPrice()->shouldBeCalled()->willReturn(79);

        $attributableOption = $this->prophesize(Product::class);
        $attributableOption->isParticipant()->shouldBeCalled()->willReturn(false);
        $attributableOption->getSerializedData()->shouldBeCalled()->willReturn('Attributable option serialized data');
        $attributableOption->isAttributable()->shouldBeCalled()->willReturn(true);
        $attributableOption->getVat()->shouldBeCalled()->willReturn(20);
        $attributableOption->getUnitPrice()->shouldBeCalled()->willReturn(120);

        $planRow = new CartRow($sheet->reveal(), $plan->reveal(), 1);
        $package->getGroupOfProduct($planRow->getProduct())->shouldBeCalled()->willReturn(null);

        $participantProductRow = new CartRow($sheet->reveal(), $participantProduct->reveal(), 1);
        $participantProductCartRowParticipant1 = new CartRowParticipant(
            $participantProductRow,
            $participant1->reveal()
        );
        $participantProductRow->addCartRowParticipant($participantProductCartRowParticipant1);
        $package->getGroupOfProduct($participantProductRow->getProduct())->shouldBeCalled()->willReturn(null);

        $chairRow = new CartRow($sheet->reveal(), $chair->reveal(), 2);
        $package->getGroupOfProduct($chairRow->getProduct())->shouldBeCalled()->willReturn(null);

        $attributableOptionRow = new CartRow($sheet->reveal(), $attributableOption->reveal(), 1);
        $attributableCartRowParticipant1 = new CartRowParticipant($attributableOptionRow, $participant1->reveal());
        $attributableOptionRow->addCartRowParticipant($attributableCartRowParticipant1);
        $package->getGroupOfProduct($attributableOptionRow->getProduct())->shouldBeCalled()->willReturn(null);

        // Expected Order
        $expectedOrder = new Order($sheet->reveal(), 'Package serialized data', $dateTime);
        $expectedOrder->addRow(new Order\Row($expectedOrder, 1, 20, $plan->reveal()));
        $expectedOrder->addRow(new Order\Row($expectedOrder, 1, 20, $participantProduct->reveal()));
        $expectedOrder->addRow(new Order\Row($expectedOrder, 2, 20, $chair->reveal()));
        $expectedOrder->addRow(new Order\Row($expectedOrder, 1, 20, $attributableOption->reveal()));

        $promotion = $this->prophesize(Promotion::class);
        $promotion->getProduct()->shouldBeCalled()->willReturn($chair->reveal());
        $promotion->getDiscountAmountForProduct($chair->reveal(), 2)->shouldBeCalled()->willReturn(-50);

        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCode->getSerializedData()->shouldBeCalled()->willReturn('Promotion Code Serialized data');
        $promotionCode->getPromotions()->shouldBeCalled()->willReturn([$promotion->reveal()]);

        $promotionCodeOrderRow = new Order\PromotionCode(
            $expectedOrder,
            $promotionCode->reveal(),
            -100,
            $chair->reveal(),
            20
        );
        $expectedOrder->addPromotionCode($promotionCodeOrderRow);

        $expectedOrderCallBack = Argument::that(function (Order $order) use ($expectedOrder) {
            return $order->getPromotionCodes()[0]->getPromotionCode() === $expectedOrder->getPromotionCodes()[0]->getPromotionCode()
                && $order->getRows()[0]->getPrice() === $expectedOrder->getRows()[0]->getPrice()
                && $order->getRows()[1]->getPrice() === $expectedOrder->getRows()[1]->getPrice()
                && $order->getRows()[2]->getPrice() === $expectedOrder->getRows()[2]->getPrice()
                && $order->getRows()[3]->getPrice() === $expectedOrder->getRows()[3]->getPrice()
            ;
        });

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->add($expectedOrderCallBack)->shouldBeCalled();
        $sheet->addOrder($expectedOrderCallBack)->shouldBeCalled();

        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->deleteForSheet($sheet->reveal())->shouldBeCalled();

        $cartStepRepository = $this->prophesize(CartStepRepositoryInterface::class);
        $cartStepRepository->deleteForSheet($sheet->reveal())->shouldBeCalled();

        $promotionCodeRowRepository = $this->prophesize(PromotionCodeRowRepositoryInterface::class);
        $promotionCodeRowRepository->deleteForSheet($sheet->reveal())->shouldBeCalled();

        $participantProductSetter = $this->prophesize(ParticipantProductSetter::class);
        $participantProductSetter
            ->setProductOnParticipant($participant1->reveal(), $participantProduct->reveal())
            ->shouldBeCalled()
        ;

        $decrementStockHandler = $this->prophesize(DecrementStockHandler::class);
        $decrementStockHandler->handle(new DecrementStock($promotionCode->reveal()))->shouldBeCalled();

        $productAttributedToParticipantSetter = $this->prophesize(ProductAttributedToParticipantSetter::class);
        $productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $attributableOption->reveal(),
                [$participant1->reveal()],
                [$participant1->reveal()]
            )
            ->shouldBeCalled()
        ;

        $converter = new Converter(
            $orderRepository->reveal(),
            $cartRowRepository->reveal(),
            $cartStepRepository->reveal(),
            $promotionCodeRowRepository->reveal(),
            $participantProductSetter->reveal(),
            $productAttributedToParticipantSetter->reveal(),
            $decrementStockHandler->reveal(),
            $dateTime
        );

        $converter->toOrder(
            new Cart(
                $sheet->reveal(),
                [
                    $planRow,
                    $participantProductRow,
                    $chairRow,
                    $attributableOptionRow,
                ],
                [
                    new PromotionCodeRow($sheet->reveal(), $promotionCode->reveal()),
                ]
            )
        );
    }
}
