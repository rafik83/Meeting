<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\CreatePackage;
use Proximum\Vimeet\Application\Command\Product\CreatePackageHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreatePackageHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
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
        $product = new Product($event, 'product', '', 100, 2, 4, 3, 4, false, null);
        $product->translate('fr', 'foo', null, 'bar', 'optional');
        $product->translate('en', 'enfoo', null, 'enbar', 'enoptional');

        $create                      = new CreatePackage($event);
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
            ]
        ];

        // Expected
        $expectedPackage = new Product(
            $event,
            Product::TYPE_PACKAGE,
            $name,
            $image,
            $unitPrice,
            1,
            $availabilityCurrent,
            $availabilityMax,
            false
        );
        $expectedPackage->translate('fr', 'foo', 'bar', 'barContent', 'optional');
        $expectedPackage->translate('en', 'enfoo', 'enbar', 'enbarContent', 'enoptional');
        $expectedPackage->includeProduct($product, 2);

        // Mock
        $pacakgeRepository = $this->prophesize(ProductRepositoryInterface::class);
        $pacakgeRepository->add($expectedPackage)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        // Handler
        $handler = new CreatePackageHandler($pacakgeRepository->reveal(), $fileStorage->reveal());
        $handler->handle($create);
    }

    public function testFeatureHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
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
        $product = new Product($event, 'product', '', 100, 2, 4, 3, 4, false, null);
        $product->translate('fr', 'foo', null, 'bar', 'optional');
        $product->translate('en', 'enfoo', null, 'enbar', 'enoptional');

        $create                      = new CreatePackage($event);
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
            ]
        ];
        $create->features = [
            [
                'translations' => [
                    'fr' => ['title' => 'Titre', 'description' => 'Description'],
                    'en' => ['title' => 'Titre', 'description' => 'Description'],
                ]
            ]
        ];

        // Expected
        $expectedPackage = new Product(
            $event,
            Product::TYPE_PACKAGE,
            $name,
            $image,
            $unitPrice,
            1,
            $availabilityCurrent,
            $availabilityMax,
            false
        );
        $expectedPackage->translate('fr', 'foo', 'bar', 'barContent', 'optional');
        $expectedPackage->translate('en', 'enfoo', 'enbar', 'enbarContent', 'enoptional');
        $expectedPackage->includeProduct($product, 2);

        $feature = new Product\Feature($expectedPackage);
        $feature->translate('fr', 'Titre', 'Description');
        $feature->translate('en', 'Titre', 'Description');
        $expectedPackage->addFeature($feature);

        // Mock
        $pacakgeRepository = $this->prophesize(ProductRepositoryInterface::class);
        $pacakgeRepository->add(Argument::that(function (Product $package) use ($expectedPackage) {
            return count($package->getFeatures()) === count($expectedPackage->getFeatures());
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        // Handler
        $handler = new CreatePackageHandler($pacakgeRepository->reveal(), $fileStorage->reveal());
        $handler->handle($create);
    }
}
