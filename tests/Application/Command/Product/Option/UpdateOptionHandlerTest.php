<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Option;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOptionHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateOptionHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
        $vat                 = 20;
        $quantityMax         = 4;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $updatable           = true;

        $option = Product::createOption(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable
        );

        $expectedOption = Product::createOption(
            $event,
            'my option updated',
            $image,
            200,
            19,
            2,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            null,
            false,
            null,
            true
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedOption->translate($locale, '', '', '', '', '');
        }

        // Command
        $updateOptionCommand = new UpdateOption($option);
        $updateOptionCommand->name = 'my option updated';
        $updateOptionCommand->quantityMax = 2;
        $updateOptionCommand->unitPrice = 200;
        $updateOptionCommand->vat = 19;
        $updateOptionCommand->attributable = true;

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedOption)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($option)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new UpdateOptionHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($updateOptionCommand);
    }

    public function testHandlePriceAndVatNotUpdatable()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
        $vat                 = 20;
        $quantityMax         = 4;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $updatable           = true;

        $option = Product::createOption(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable
        );

        $expectedOption = Product::createOption(
            $event,
            'my option updated',
            $image,
            $unitPrice,
            $vat,
            2,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            null,
            false,
            null,
            true
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedOption->translate($locale, '', '', '', '', '');
        }

        // Command
        $updateOptionCommand = new UpdateOption($option);
        $updateOptionCommand->name = 'my option updated';
        $updateOptionCommand->quantityMax = 2;
        $updateOptionCommand->unitPrice = 200;
        $updateOptionCommand->vat = 10;
        $updateOptionCommand->attributable = true;

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedOption)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($option)->shouldBeCalled()->willReturn(false);

        // Handler
        $handler = new UpdateOptionHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($updateOptionCommand);
    }
}
