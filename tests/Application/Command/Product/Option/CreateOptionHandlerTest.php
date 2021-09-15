<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Option;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Option\CreateOption;
use Proximum\Vimeet\Application\Command\Product\Option\CreateOptionHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateOptionHandlerTest extends TestCase
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
        $canScanParticipant  = true;
        $deletableUntil      = new \DateTime();
        $translations = [
            'fr' => [
                'title'                     => 'foo',
                'description'               => 'bar',
                'addon'                     => 'optional',
                'subjectedToValidationHelp' => '',
            ],
            'en' => [
                'title'                     => 'enfoo',
                'description'               => 'enbar',
                'addon'                     => 'enoptional',
                'subjectedToValidationHelp' => '',
            ],
        ];

        $create                      = new CreateOption($event);
        $create->name                = $name;
        $create->vat                 = 20;
        $create->unitPrice           = $unitPrice;
        $create->quantityMax         = $quantityMax;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->updatable           = $updatable;
        $create->deletableUntil      = $deletableUntil;
        $create->translations        = $translations;
        $create->attributable        = true;
        $create->canScanParticipant  = true;
        $create->file                = null;

        // Expected
        $expectedProduct = Product::createOption(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            $deletableUntil,
            false,
            null,
            true,
            $canScanParticipant
        );
        $expectedProduct->translate('fr', 'foo', null, 'bar', 'optional', '');
        $expectedProduct->translate('en', 'enfoo', null, 'enbar', 'enoptional', '');

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->add($expectedProduct)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);

        // Handler
        $handler = new CreateOptionHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($create);
    }

    public function testOptionBuyable()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $now = new \DateTime();

        $name = 'Name';
        $image = 'Image';
        $unitPrice = 100;
        $quantityMax = 4;
        $vat = 20;
        $availabilityCurrent = 10;
        $availabilityMax = 50;
        $updatable = true;
        $updatableUntil = new \DateTime();
        $buyableUntil = $now->modify('-1 day');

        $product = Product::createOption(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            $updatableUntil,
            false,
            $buyableUntil
        );

        $this->assertEquals(false, $product->isBuyable(new \DateTime()));
    }
}
