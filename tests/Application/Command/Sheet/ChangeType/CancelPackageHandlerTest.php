<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\ChangeType;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackage;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackageHandler;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductsAttributedToParticipantRemoveAllBySheet;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class CancelPackageHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $orderRepository;

    /** @var ObjectProphecy */
    private $cartManager;

    /** @var ObjectProphecy */
    private $participantProductSetter;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $productsAttributedToParticipantRemoveAllBySheet;

    public function setUp(): void
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->participantProductSetter = $this->prophesize(ParticipantProductSetter::class);
        $this->productsAttributedToParticipantRemoveAllBySheet = $this->prophesize(
            ProductsAttributedToParticipantRemoveAllBySheet::class
        );
    }

    public function testWithoutOrder(): void
    {
        $this->orderRepository->findBySheet($this->sheet->reveal())->shouldBeCalled()->willReturn([]);
        $this->cartManager->emptyCart($this->sheet->reveal())->shouldBeCalled();

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $this->sheet->getParticipantsArray()->willReturn([$participant1->reveal(), $participant2->reveal()]);
        $this->participantProductSetter->setProductOnParticipant($participant1->reveal(), null)->shouldBeCalled();
        $this->participantProductSetter->setProductOnParticipant($participant2->reveal(), null)->shouldBeCalled();

        $this->productsAttributedToParticipantRemoveAllBySheet->handle($this->sheet->reveal())->shouldBeCalled();

        $command = new CancelPackage($this->sheet->reveal());
        $handler = new CancelPackageHandler(
            $this->orderRepository->reveal(),
            $this->cartManager->reveal(),
            $this->participantProductSetter->reveal(),
            $this->productsAttributedToParticipantRemoveAllBySheet->reveal()
        );
        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $order = $this->prophesize(Order::class);
        $order->cancel()->shouldBeCalled();
        $this->orderRepository->set($order->reveal())->shouldBeCalled();

        $this->orderRepository->findBySheet($this->sheet->reveal())->shouldBeCalled()->willReturn([$order]);
        $this->cartManager->emptyCart($this->sheet->reveal())->shouldBeCalled();

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $this->sheet->getParticipantsArray()->willReturn([$participant1->reveal(), $participant2->reveal()]);
        $this->participantProductSetter->setProductOnParticipant($participant1->reveal(), null)->shouldBeCalled();
        $this->participantProductSetter->setProductOnParticipant($participant2->reveal(), null)->shouldBeCalled();

        $this->productsAttributedToParticipantRemoveAllBySheet->handle($this->sheet->reveal())->shouldBeCalled();

        $command = new CancelPackage($this->sheet->reveal());
        $handler = new CancelPackageHandler(
            $this->orderRepository->reveal(),
            $this->cartManager->reveal(),
            $this->participantProductSetter->reveal(),
            $this->productsAttributedToParticipantRemoveAllBySheet->reveal()
        );
        $handler->handle($command);
    }
}
