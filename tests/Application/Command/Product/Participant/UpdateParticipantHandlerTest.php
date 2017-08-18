<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
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
        $quantityMax = 4;

        $participant = Product::createParticipant(
            $event,
            $name,
            $unitPrice,
            $quantityMax
        );

        $expectedParticipant = Product::createParticipant(
            $event,
            'my participant updated',
            $unitPrice,
            $quantityMax
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedParticipant->translate($locale, '', '', '', '', '');
        }

        // Command
        $updateParticipantCommand = new UpdateParticipant($participant);
        $updateParticipantCommand->name = 'my participant updated';

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedParticipant)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($participant)->shouldBeCalled();

        // Handler
        $handler = new UpdateParticipantHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($updateParticipantCommand);
    }
}
