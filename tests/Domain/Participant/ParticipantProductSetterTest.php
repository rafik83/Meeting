<?php

namespace Proximum\Vimeet\Tests\Domain\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ProductSetOnParticipantEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParticipantProductSetterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $product;

    /** @var ObjectProphecy|ParticipantRepositoryInterface */
    private $participantRepository;

    public function setUp()
    {
        $this->participant = $this->prophesize(Participant::class);
        $this->product = $this->prophesize(Product::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
    }

    public function testSetProductOnParticipantWithoutPreviousProduct(): void
    {
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn(null);
        $this->participant->setParticipantProduct($this->product->reveal())->shouldBeCalled();
        $this->participantRepository->set($this->participant->reveal())->shouldBeCalled();

        $event = new ProductSetOnParticipantEvent($this->participant->reveal());
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, $event)->shouldBeCalled();

        $participantProductSetter = new ParticipantProductSetter(
            $this->participantRepository->reveal(),
            $this->eventDispatcher->reveal()
        );
        $participantProductSetter->setProductOnParticipant($this->participant->reveal(), $this->product->reveal());
    }

    public function testSetProductOnParticipantWithDifferentPreviousProduct(): void
    {
        $product = $this->prophesize(Product::class);
        $product->getId()->willReturn(12);
        $this->product->getId()->willReturn(15);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($product->reveal());
        $this->participant->setParticipantProduct($this->product->reveal())->shouldBeCalled();
        $this->participantRepository->set($this->participant->reveal())->shouldBeCalled();

        $event = new ProductSetOnParticipantEvent($this->participant->reveal());
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, $event)->shouldBeCalled();

        $participantProductSetter = new ParticipantProductSetter(
            $this->participantRepository->reveal(),
            $this->eventDispatcher->reveal()
        );
        $participantProductSetter->setProductOnParticipant($this->participant->reveal(), $this->product->reveal());
    }

    public function testSetProductOnParticipantWithSamePreviousProduct(): void
    {
        $this->product->getId()->willReturn(15);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($this->product->reveal());
        $this->participant->setParticipantProduct($this->product->reveal())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, Argument::any())->shouldNotBeCalled();

        $participantProductSetter = new ParticipantProductSetter(
            $this->participantRepository->reveal(),
            $this->eventDispatcher->reveal()
        );
        $participantProductSetter->setProductOnParticipant($this->participant->reveal(), $this->product->reveal());
    }
}
