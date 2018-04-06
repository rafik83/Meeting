<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ProductSetOnParticipantEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;

class ParticipantProductSetterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $product;

    public function setUp()
    {
        $this->participant = $this->prophesize(Participant::class);
        $this->product = $this->prophesize(Product::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testSetProductOnParticipantWithoutPreviousProduct(): void
    {
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn(null);
        $this->participant->setParticipantProduct($this->product->reveal())->shouldBeCalled();

        $event = new ProductSetOnParticipantEvent($this->participant->reveal());
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, $event)->shouldBeCalled();

        $participantProductSetter = new ParticipantProductSetter($this->eventDispatcher->reveal());
        $participantProductSetter->setProductOnParticipant($this->participant->reveal(), $this->product->reveal());
    }

    public function testSetProductOnParticipantWithDifferentPreviousProduct(): void
    {
        $product = $this->prophesize(Product::class);
        $product->getId()->willReturn(12);
        $this->product->getId()->willReturn(15);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($product->reveal());
        $this->participant->setParticipantProduct($this->product->reveal())->shouldBeCalled();

        $event = new ProductSetOnParticipantEvent($this->participant->reveal());
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, $event)->shouldBeCalled();

        $participantProductSetter = new ParticipantProductSetter($this->eventDispatcher->reveal());
        $participantProductSetter->setProductOnParticipant($this->participant->reveal(), $this->product->reveal());
    }

    public function testSetProductOnParticipantWithSamePreviousProduct(): void
    {
        $this->product->getId()->willReturn(15);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($this->product->reveal());
        $this->participant->setParticipantProduct($this->product->reveal())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, Argument::any())->shouldNotBeCalled();

        $participantProductSetter = new ParticipantProductSetter($this->eventDispatcher->reveal());
        $participantProductSetter->setProductOnParticipant($this->participant->reveal(), $this->product->reveal());
    }
}
