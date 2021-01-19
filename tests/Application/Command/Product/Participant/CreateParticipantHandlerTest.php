<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\CreateParticipant;
use Proximum\Vimeet\Application\Command\Product\Participant\CreateParticipantHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateParticipantHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $unitPrice = 100;
        $vat = 20;
        $quantityMax = 4;
        $availabilityCurrent = 10;
        $availabilityMax = 50;
        $updatable = true;
        $updatableUntil = new \DateTime();
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

        $create = new CreateParticipant($event);
        $create->name = $name;
        $create->unitPrice = $unitPrice;
        $create->vat = $vat;
        $create->quantityMax = $quantityMax;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax = $availabilityMax;
        $create->updatable = $updatable;
        $create->updatableUntil = $updatableUntil;
        $create->translations = $translations;
        $create->file = null;

        // Expected
        $expectedProduct = Product::createParticipant(
            $event,
            $name,
            $unitPrice,
            $vat,
            $quantityMax
        );

        $expectedProduct->translate('fr', 'foo', null, 'bar', 'optional', '');
        $expectedProduct->translate('en', 'enfoo', null, 'enbar', 'enoptional', '');

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->add(Argument::that(function (Product $product) use ($expectedProduct) {
            $this->assertEquals($product->getName(), $expectedProduct->getName());
            $this->assertEquals($product->getUnitPrice(), $expectedProduct->getUnitPrice());
            $this->assertEquals($product->getQuantityMax(), $expectedProduct->getQuantityMax());
            $this->assertEquals($product->getTitle('fr'), $expectedProduct->getTitle('fr'));

            return true;
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldNotBeCalled();

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);

        // Handler
        $handler = new CreateParticipantHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($create);
    }
}
