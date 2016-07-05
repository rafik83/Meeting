<?php


namespace Proximum\Vimeet\Tests\Application\Command\Product\Planning;


use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanning;
use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanningHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdatePlanningHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $unitPrice = 100;
        $quantityMax = 4;

        $planning = Product::createPlanning(
            $event,
            $name,
            $unitPrice,
            $quantityMax
        );

        $expectedPlanning = Product::createPlanning(
            $event,
            'my planning updated',
            $unitPrice,
            $quantityMax
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedPlanning->translate($locale, '', '', '', '', '');
        }

        // Command
        $updatePlanningCommand = new UpdatePlanning($planning);
        $updatePlanningCommand->name = 'my planning updated';

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedPlanning)->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        // Handler
        $handler = new UpdatePlanningHandler($productRepository->reveal(), $fileStorage->reveal());
        $handler->handle($updatePlanningCommand);
    }
}
