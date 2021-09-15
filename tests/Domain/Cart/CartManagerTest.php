<?php

namespace Proximum\Vimeet\Tests\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Cart\ParticipantCartRowAddedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class CartManagerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $cartRowRepository;

    /** @var ObjectProphecy */
    private $cartStepRepository;

    /** @var ObjectProphecy */
    private $promotionCodeRowRepository;

    /** @var ObjectProphecy */
    private $orderMerger;

    /** @var ObjectProphecy */
    private $participantProductSetter;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $productAttributedToParticipantRepository;

    /** @var ObjectProphecy */
    private $productAttributedToParticipantSetter;

    public function setUp()
    {
        $this->cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $this->cartStepRepository = $this->prophesize(CartStepRepositoryInterface::class);
        $this->promotionCodeRowRepository = $this->prophesize(PromotionCodeRowRepositoryInterface::class);
        $this->orderMerger = $this->prophesize(Merger::class);
        $this->participantProductSetter = $this->prophesize(ParticipantProductSetter::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->productAttributedToParticipantRepository = $this->prophesize(ProductAttributedToParticipantRepositoryInterface::class);
        $this->productAttributedToParticipantSetter = $this->prophesize(ProductAttributedToParticipantSetter::class);
    }

    public function testDeleteCartStep()
    {
        $cart = $this->prophesize(Cart::class);
        $sheet = $this->prophesize(Sheet::class);
        $cart->getSheet()->willReturn($sheet->reveal());

        // Mock
        $this->cartStepRepository->deleteForSheet($sheet->reveal())->shouldBeCalled();

        // CartManager
        $cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal(),
            $this->participantProductSetter->reveal(),
            $this->productAttributedToParticipantRepository->reveal(),
            $this->productAttributedToParticipantSetter->reveal(),
            $this->eventDispatcher->reveal()
        );

        $cartManager->deleteCartStep($cart->reveal());
    }

    public function testGetCart()
    {
        $cart = $this->prophesize(Cart::class);
        $sheet = $this->prophesize(Sheet::class);
        $cart->getSheet()->willReturn($sheet->reveal());
        $cartRow = $this->prophesize(CartRow::class);

        // Mock
        $this->cartRowRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$cartRow->reveal()]);
        $this->promotionCodeRowRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([]);

        // CartManager
        $cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal(),
            $this->participantProductSetter->reveal(),
            $this->productAttributedToParticipantRepository->reveal(),
            $this->productAttributedToParticipantSetter->reveal(),
            $this->eventDispatcher->reveal()
        );

        $result = $cartManager->getCart($sheet->reveal(), 3);

        $expected = new Cart($sheet->reveal(), [$cartRow->reveal()], [], 3);

        $this->assertEquals($expected, $result);
    }

    public function testUpdateParticipantsQuantityWithIncludedProduct()
    {
        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct1->getId()->shouldBeCalled()->willReturn(111);
        $participantProduct1->getSerializedData()->willReturn('participantProduct1');
        $participantProduct1->isParticipant()->willReturn(true);
        $participantProduct1->isAttributable()->willReturn(false);
        $participantProduct2 = $this->prophesize(Product::class);
        $participantProduct2->getId()->shouldBeCalled()->willReturn(222);
        $participantProduct2->isAttributable()->willReturn(false);

        $participantProductIncluded = $this->prophesize(ProductIncluded::class);
        $participantProductIncluded->getIncluded()->shouldBeCalled()->willReturn($participantProduct2->reveal());
        $participantProductIncluded->getQuantity()->shouldBeCalled()->willReturn(1);

        $order = $this->prophesize(Order::class);
        $order->getIncludedParticipantProducts()->shouldBeCalled()->willReturn([$participantProductIncluded->reveal()]);
        $order->getRowByProductId(111)->shouldBeCalled()->willReturn(null);
        $order->getRowByProductId(222)->shouldBeCalled()->willReturn(null);
        $order->getRowsProductOfParticipantType()->shouldBeCalled()->willReturn([]);

        $package = $this->prophesize(Package::class);
        $package
            ->getParticipants()
            ->shouldBeCalled()
            ->willReturn([$participantProduct1->reveal(), $participantProduct2->reveal()])
        ;

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(963);
        $participant1->hasParticipantProduct()->willReturn(false);

        $participantCartRowAddedEvent = new ParticipantCartRowAddedEvent($participant1->reveal(), false, false);
        $this->eventDispatcher
            ->dispatch(Events::PARTICIPANT_CART_ROW_ADDED, $participantCartRowAddedEvent)
            ->shouldBeCalled()
        ;
        $this->participantProductSetter->setProductOnParticipant($participant1, Argument::any())->shouldNotBeCalled();

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(1337);
        $this->participantProductSetter
            ->setProductOnParticipant($participant2->reveal(), $participantProduct2->reveal())
            ->shouldBeCalled()
        ;

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());
        $sheet
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $participant1->reveal(),
                    $participant2->reveal(),
                ]
            )
        ;

        $cart = new Cart(
            $sheet->reveal(),
            [],
            []
        );

        $this->orderMerger->getMergedOrders($sheet->reveal())->shouldBeCalled()->willReturn($order->reveal());

        // array of participantId => Product
        $productByParticipantId = [
            963 => $participantProduct1->reveal(),
            1337 => $participantProduct2->reveal(),
        ];

        $cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal(),
            $this->participantProductSetter->reveal(),
            $this->productAttributedToParticipantRepository->reveal(),
            $this->productAttributedToParticipantSetter->reveal(),
            $this->eventDispatcher->reveal()
        );

        $result = $cartManager->updateParticipantsQuantity($cart, $productByParticipantId);

        $expectedCartRow = new CartRow($sheet->reveal(), $participantProduct1->reveal(), 1);
        $expectedCartRow->addCartRowParticipant(new CartRowParticipant($expectedCartRow, $participant1->reveal()));
        $expected = new Cart($sheet->reveal(), [$expectedCartRow], []);

        $this->assertEquals($expected, $result);
    }
}
