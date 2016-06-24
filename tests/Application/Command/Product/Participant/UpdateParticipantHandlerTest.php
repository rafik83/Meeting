<?php


namespace Application\Command\Product\Participant;


use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipantHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class UpdateParticipantHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
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

        // Handler
        $handler = new UpdateParticipantHandler($productRepository->reveal(), $fileStorage->reveal());
        $handler->handle($updateParticipantCommand);
    }
}
