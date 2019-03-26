<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipantHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Product\ProductUpdatedEvent;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateParticipantHandlerTest extends TestCase
{
    public function testHandle()
    {
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $product = $this->prophesize(Product::class);

        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $unitPrice = 100;
        $vat = 20;
        $quantityMax = 4;
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 18:00:00.000');
        $availabilityTimeRange = new AvailabilityTimeRange($event, 'name', $begin, $end);

        $participant = Product::createParticipant(
            $event,
            $name,
            $unitPrice,
            $vat,
            $quantityMax
        );

        $expectedParticipant = Product::createParticipant(
            $event,
            'my participant updated',
            200,
            19,
            $quantityMax
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedParticipant->translate($locale, '', '', '', '', '');
        }
        $expectedParticipant->setAvailabilityTimeRanges([$availabilityTimeRange]);

        // Command
        $updateParticipantCommand = new UpdateParticipant($participant);
        $updateParticipantCommand->name = 'my participant updated';
        $updateParticipantCommand->unitPrice = 200;
        $updateParticipantCommand->vat = 19;
        $updateParticipantCommand->availabilityTimeRanges = [
            $availabilityTimeRange,
        ];

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository
            ->update(Argument::that(function (Product $product) use ($expectedParticipant) {
                return $product->getEvent() === $expectedParticipant->getEvent()
                    && $product->getName() === $expectedParticipant->getName()
                    && $product->getVat() === $expectedParticipant->getVat()
                    && $product->getTitle('fr') === $expectedParticipant->getTitle('fr')
                    && \count($product->getAvailabilityTimeRanges()) === \count($expectedParticipant->getAvailabilityTimeRanges())
                    && $product->getQuantityMax() === $expectedParticipant->getQuantityMax()
                ;
            }))
            ->shouldBeCalled();

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($participant)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new UpdateParticipantHandler(
            $eventDispatcher->reveal(),
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($updateParticipantCommand);

        $eventDispatcher->reveal()->dispatch(
            Events::PRODUCT_UPDATED,
            new ProductUpdatedEvent($product->reveal(),[$availabilityTimeRange])
        );
    }

    public function testHandleCanNotUpdatePriceAndVat()
    {
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $unitPrice = 100;
        $vat = 20;
        $quantityMax = 4;

        $participant = Product::createParticipant(
            $event,
            $name,
            $unitPrice,
            $vat,
            $quantityMax
        );

        $expectedParticipant = Product::createParticipant(
            $event,
            'my participant updated',
            $unitPrice,
            $vat,
            $quantityMax
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedParticipant->translate($locale, '', '', '', '', '');
        }

        // Command
        $updateParticipantCommand = new UpdateParticipant($participant);
        $updateParticipantCommand->name = 'my participant updated';
        $updateParticipantCommand->unitPrice = 200;
        $updateParticipantCommand->vat = 19;

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedParticipant)->shouldBeCalled();

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($participant)->shouldBeCalled()->willReturn(false);

        // Handler
        $handler = new UpdateParticipantHandler(
            $eventDispatcher->reveal(),
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($updateParticipantCommand);
    }
}
