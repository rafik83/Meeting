<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Planning;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Planning\CreatePlanning;
use Proximum\Vimeet\Application\Command\Product\Planning\CreatePlanningHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreatePlanningHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $unitPrice           = 100;
        $quantityMax         = 4;
        $vat                 = 20;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $updatable           = true;
        $updatableUntil      = new \DateTime();

        $create                      = new CreatePlanning($event);
        $create->name                = $name;
        $create->unitPrice           = $unitPrice;
        $create->vat                 = $vat;
        $create->quantityMax         = $quantityMax;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->updatable           = $updatable;
        $create->updatableUntil      = $updatableUntil;
        $create->file                = null;

        // Expected
        $expectedProduct = Product::createPlanning(
            $event,
            $name,
            $unitPrice,
            $vat,
            $quantityMax
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedProduct->translate($locale, '', '', '', '', '');
        }

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->add($expectedProduct)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldNotBeCalled();

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);

        // Handler
        $handler = new CreatePlanningHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($create);
    }
}
