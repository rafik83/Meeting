<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipantHandler;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class UpdateParticipantHandlerTest extends TestCase
{
    public function testHandle()
    {
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
            200,
            19,
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
        $updatePriceResolver->resolve($participant)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new UpdateParticipantHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($updateParticipantCommand);
    }

    public function testHandleCanNotUpdatePriceAndVat()
    {
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
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($updateParticipantCommand);
    }
}
