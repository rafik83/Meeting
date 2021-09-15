<?php

namespace Proximum\Vimeet\Tests\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class CartManagerUpdateOptionsQuantityTest extends TestCase
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
    private $productAttributedToParticipantRepository;

    /** @var ObjectProphecy */
    private $productAttributedToParticipantSetter;

    /** @var ObjectProphecy */
    private $delayedEventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $cart;

    /** @var ObjectProphecy */
    private $product;

    /** @var ObjectProphecy */
    private $participant1;

    /** @var ObjectProphecy */
    private $participant2;

    /** @var ObjectProphecy */
    private $participant3;

    /** @var array */
    private $participants;

    /** @var ObjectProphecy */
    private $cartManager;

    public function setUp()
    {
        $this->cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $this->cartStepRepository = $this->prophesize(CartStepRepositoryInterface::class);
        $this->promotionCodeRowRepository = $this->prophesize(PromotionCodeRowRepositoryInterface::class);
        $this->orderMerger = $this->prophesize(Merger::class);
        $this->participantProductSetter = $this->prophesize(ParticipantProductSetter::class);
        $this->productAttributedToParticipantRepository = $this->prophesize(
            ProductAttributedToParticipantRepositoryInterface::class
        );
        $this->productAttributedToParticipantSetter = $this->prophesize(ProductAttributedToParticipantSetter::class);
        $this->delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->dateTime = new \DateTime();
        $this->sheet = $this->prophesize(Sheet::class);
        $this->cart = $this->prophesize(Cart::class);
        $this->product = $this->prophesize(Product::class);

        $this->cart->getSheet()->willReturn($this->sheet->reveal());

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant3 = $this->prophesize(Participant::class);

        $this->participant1->getId()->willReturn(91);
        $this->participant2->getId()->willReturn(92);
        $this->participant3->getId()->willReturn(93);

        $this->participants = [
            $this->participant1->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ];

        $this->sheet->getParticipantsArray()->willReturn($this->participants);

        $this->cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal(),
            $this->participantProductSetter->reveal(),
            $this->productAttributedToParticipantRepository->reveal(),
            $this->productAttributedToParticipantSetter->reveal(),
            $this->delayedEventDispatcher->reveal()
        );
    }

    public function testNotAttributableWithQuantity(): void
    {
        $this->cart->setProduct($this->product->reveal(), 2)->shouldBeCalled();
        $this->product->isAttributable()->shouldBeCalled()->willReturn(false);

        $optionRow = new OptionRow(2, [], false);

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            []
        );
    }

    public function testNotAttributableWithPreviousOrderedQuantity(): void
    {
        $this->cart->setProduct($this->product->reveal(), 1)->shouldBeCalled();
        $this->product->isAttributable()->shouldBeCalled()->willReturn(false);
        $this->product->getId()->shouldBeCalled()->willReturn(1337);

        $row = $this->prophesize(Order\Row::class);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $order = $this->prophesize(Order::class);
        $order->getRowByProductId(1337)->shouldBeCalled()->willReturn($row);

        $optionRow = new OptionRow(2, [], false);

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            []
        );
    }

    public function testNotAttributable(): void
    {
        $this->cart->setProduct($this->product->reveal(), 0)->shouldBeCalled();
        $this->product->isAttributable()->shouldBeCalled()->willReturn(false);

        $optionRow = new OptionRow(0, [], false);

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            []
        );
    }

    public function testNoQuantitySelected(): void
    {
        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $optionRow = new OptionRow(0, [], true);

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            []
        );
    }

    public function testNoQuantitySelectedButIncludedQuantity(): void
    {
        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                []
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(0, [], true);

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            [12 => 2]
        );
    }

    public function testNoQuantitySelectedNoIncludedButPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $optionRow = new OptionRow(0, [], true);
        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart
            ->setProduct(
                $this->product->reveal(),
                -1,
                []
            )
            ->shouldBeCalled()
        ;

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            []
        );
    }

    public function testQuantitySelectedNoIncludedAndPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [$this->participant2->reveal()]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(1, [$this->participant2->reveal()], true);
        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            []
        );
    }

    public function testQuantitySelectedAndIncludedButNoPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [$this->participant2->reveal()]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(1, [$this->participant2->reveal()], true);
        $order = $this->prophesize(Order::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn(null);
        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 1]
        );
    }

    public function testQuantitySelectedAndIncludedAndPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ],
            true
        );
        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 1]
        );
    }

    public function testQuantitySelectedAndIncludedAndPreviousOrderQuantityButNoChange(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
            ],
            true
        );
        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 1]
        );
    }

    public function testQuantitySelectedLowerThanIncludedAndPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(Argument::any())
            ->shouldNotBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
            ],
            true
        );
        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart
            ->setProduct(
                $this->product->reveal(),
                -1,
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 2]
        );
    }

    public function testQuantitySelectedHigherThanIncludedAndPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(Argument::any())
            ->shouldNotBeCalled()
        ;

        $optionRow = new OptionRow(
            3,
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ],
            true
        );
        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart
            ->setProduct(
                $this->product->reveal(),
                1,
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 1]
        );
    }

    public function testQuantitySelectedAndIncludedWithNoPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ],
            true
        );
        $order = $this->prophesize(Order::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn(null);

        $this->cart->setProduct(Argument::any())->shouldNotBeCalled();

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 3]
        );
    }

    public function testQuantitySelectedHigherThanIncludedAndPreviousOrderButNoOrderedQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(Argument::any())
            ->shouldNotBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ],
            true
        );
        $order = $this->prophesize(Order::class);
        $order->getRowByProductId(12)->shouldBeCalled()->willReturn(null);

        $this->cart->setProduct(
            $this->product->reveal(),
            1,
            [
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ]
        );

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 1]
        );
    }

    public function testQuantitySelectedHigherThanIncludedAndNoOrderAndNoOrderedQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $optionRow = new OptionRow(
            2,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ],
            true
        );

        $this->cart->setProduct(
            $this->product->reveal(),
            1,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ]
        );

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            [12 => 1]
        );
    }

    public function testQuantitySelectedHigherThanIncludedAndNoOrderAndNoOrderedQuantityAndNotTheSameNumberOfProducAttributedAndIncluded(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [$this->participant3->reveal()]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ],
            true
        );

        $this->cart->setProduct(
            $this->product->reveal(),
            1,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ]
        );

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            [12 => 1]
        );
    }

    public function testQuantitySelectedHigherThanIncludedAndNoOrderAndNoOrderedQuantityAndDifferentChoice(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [$this->participant3->reveal()]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ],
            true
        );

        $this->cart->setProduct(
            $this->product->reveal(),
            1,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ]
        );

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            [12 => 1]
        );
    }

    public function testQuantitySelectedHigherThanIncludedAndNoOrderAndNoOrderedQuantityAndDifferentChoiceAndNoProductAttributed(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $this
            ->productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $this->product->reveal(),
                $this->participants,
                [$this->participant3->reveal()]
            )
            ->shouldBeCalled()
        ;

        $optionRow = new OptionRow(
            2,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ],
            true
        );

        $this->cart->setProduct(
            $this->product->reveal(),
            1,
            [
                $this->participant3->reveal(),
                $this->participant1->reveal(),
            ]
        );

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            null,
            [12 => 1]
        );
    }

    public function testNoQuantitySelectedAndIncludedAndPreviousOrderQuantity(): void
    {
        $this->product->isAttributable()->shouldBeCalled()->willReturn(true);
        $this->product->getId()->shouldBeCalled()->willReturn(12);

        $order = $this->prophesize(Order::class);
        $row = $this->prophesize(Order\Row::class);

        $order->getRowByProductId(12)->shouldBeCalled()->willReturn($row);
        $row->getQuantity()->shouldBeCalled()->willReturn(1);

        $this->cart->setProduct($this->product->reveal(), -1, []);

        $optionRow = new OptionRow(0, [], true);

        $this->cartManager->updateOptionsQuantity(
            $this->cart->reveal(),
            $optionRow,
            $this->product->reveal(),
            $order->reveal(),
            [12 => 1]
        );
    }
}
