<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Plan;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Plan\CreatePlan;
use Proximum\Vimeet\Application\Command\Product\Plan\CreatePlanHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreatePlanHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
        $vat                 = 20;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $translations = [
            'fr' => [
                'title'       => 'foo',
                'heading'     => 'bar',
                'description' => 'barContent',
                'addon'       => 'optional',
            ],
            'en' => [
                'title'       => 'enfoo',
                'heading'     => 'enbar',
                'description' => 'enbarContent',
                'addon'       => 'enoptional',
            ],
        ];
        $product = Product::createOption($event, 'Option A', 'a.jpg', 100, 20, 2, 4, 3, false);
        $product->translate('fr', 'foo', null, 'bar', 'optional', null);
        $product->translate('en', 'enfoo', null, 'enbar', 'enoptional', null);

        $create                      = new CreatePlan($event);
        $create->name                = $name;
        $create->unitPrice           = $unitPrice;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->translations        = $translations;
        $create->file                = null;
        $create->productIncluded = [
            [
                'product'  => $product,
                'quantity' => 2,
            ],
        ];

        // Expected
        $expectedPlan = Product::createPlan(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $availabilityCurrent,
            $availabilityMax
        );
        $expectedPlan->translate('fr', 'foo', 'bar', 'barContent', 'optional', null);
        $expectedPlan->translate('en', 'enfoo', 'enbar', 'enbarContent', 'enoptional', null);
        $expectedPlan->includeProduct($product, 2);

        // Mock
        $pacakgeRepository = $this->prophesize(ProductRepositoryInterface::class);
        $pacakgeRepository->add($expectedPlan)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);

        // Handler
        $handler = new CreatePlanHandler(
            $pacakgeRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($create);
    }

    public function testFeatureHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
        $vat                 = 19.6;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $translations = [
            'fr' => [
                'title'       => 'foo',
                'heading'     => 'bar',
                'description' => 'barContent',
                'addon'       => 'optional',
            ],
            'en' => [
                'title'       => 'enfoo',
                'heading'     => 'enbar',
                'description' => 'enbarContent',
                'addon'       => 'enoptional',
            ],
        ];
        $product = Product::createOption($event, 'Option A', 'a.jpg', 100, $vat, 2, 4, 3, false);
        $product->translate('fr', 'foo', null, 'bar', 'optional', null);
        $product->translate('en', 'enfoo', null, 'enbar', 'enoptional', null);

        $create                      = new CreatePlan($event);
        $create->name                = $name;
        $create->unitPrice           = $unitPrice;
        $create->vat                 = $vat;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->translations        = $translations;
        $create->file                = null;
        $create->productIncluded = [
            [
                'product'  => $product,
                'quantity' => 2,
            ],
        ];
        $create->features = [
            [
                'translations' => [
                    'fr' => ['title' => 'Titre', 'description' => 'Description'],
                    'en' => ['title' => 'Titre', 'description' => 'Description'],
                ],
            ],
        ];

        // Expected
        $expectedPlan = Product::createPlan(
            $event,
            $name,
            $image,
            $unitPrice,
            $vat,
            $availabilityCurrent,
            $availabilityMax
        );
        $expectedPlan->translate('fr', 'foo', 'bar', 'barContent', 'optional', null);
        $expectedPlan->translate('en', 'enfoo', 'enbar', 'enbarContent', 'enoptional', null);
        $expectedPlan->includeProduct($product, 2);

        $feature = new Product\Feature($expectedPlan);
        $feature->translate('fr', 'Titre', 'Description');
        $feature->translate('en', 'Titre', 'Description');
        $expectedPlan->addFeature($feature);

        // Mock
        $pacakgeRepository = $this->prophesize(ProductRepositoryInterface::class);
        $pacakgeRepository->add(Argument::that(function (Product $plan) use ($expectedPlan) {
            return count($plan->getFeatures()) === count($expectedPlan->getFeatures());
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);

        // Handler
        $handler = new CreatePlanHandler(
            $pacakgeRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($create);
    }
}
