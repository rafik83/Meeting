<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Plan;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlan;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlanHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdatePlanHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $image = 'Image';
        $unitPrice = 100;
        $vat = 20;
        $availabilityCurrent = 10;
        $availabilityMax = 50;

        $product = Product::createOption($event, 'Option A', 'a.jpg', 100, 20, 2, 4, 3, false);
        $product->translate('fr', 'foo', null, 'bar', 'optional', null);
        $product->translate('en', 'enfoo', null, 'enbar', 'enoptional', null);

        $plan = Product::createPlan(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $availabilityCurrent,
            $availabilityMax
        );

        $expectedPlan = Product::createPlan(
            $event,
            'name plan modify',
            $image,
            200,
            19,
            $availabilityCurrent,
            $availabilityMax
        );
        $expectedPlan->includeProduct($product, 2);
        $expectedPlan->translate('fr', 'title fr', 'heading fr', 'description fr', 'addon fr', null);
        $expectedPlan->translate('en', 'title en', 'heading en', 'description en', 'addon en', null);

        $updateCommand = new UpdatePlan($plan);
        $updateCommand->name = 'name plan modify';
        $updateCommand->unitPrice = 200;
        $updateCommand->vat = 19;
        $updateCommand->translations = [
            'fr' => [
                'title'       => 'title fr',
                'heading'     => 'heading fr',
                'description' => 'description fr',
                'addon'       => 'addon fr',
            ],
            'en' => [
                'title'       => 'title en',
                'heading'     => 'heading en',
                'description' => 'description en',
                'addon'       => 'addon en',
            ],
        ];
        $updateCommand->productIncluded = [
            [
                'product' => $product,
                'quantity' => 2,
            ],
        ];

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedPlan)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($plan)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new UpdatePlanHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($updateCommand);
    }
}
