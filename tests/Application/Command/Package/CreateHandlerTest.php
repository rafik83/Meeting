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
use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Application\Command\Package\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageTranslation;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductTranslation;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
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
        $participantIncluded = 2;
        $translations = [
            'fr' => [
                'title'              => 'foo',
                'descriptionTitle'   => 'bar',
                'descriptionContent' => 'barContent',
                'optionalPriceText'  => 'optional',
            ],
            'en' => [
                'title'              => 'enfoo',
                'descriptionTitle'   => 'enbar',
                'descriptionContent' => 'enbarContent',
                'optionalPriceText'  => 'enoptional',
            ],
        ];
        $product = new Product($event, 'product', '', 100, 2, 4, 3, 4, false, null);
        $product->getTranslations()->set(
            'fr',
            new ProductTranslation(
                $product,
                'fr',
                'foo',
                'bar',
                'optional'
            )
        );
        $product->getTranslations()->set(
            'en',
            new ProductTranslation(
                $product,
                'en',
                'enfoo',
                'enbar',
                'enoptional'
            )
        );

        $create                      = new Create($event);
        $create->name                = $name;
        $create->unitPrice           = $unitPrice;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->translations        = $translations;
        $create->file                = null;
        $create->participantIncluded = $participantIncluded;
        $create->productIncluded = [
            0 => [
                'product'  => $product,
                'quantity' => 2,
            ]
        ];

        // Expected
        $expectedPackage = new Package(
            $event,
            $name,
            $image,
            $unitPrice,
            $availabilityCurrent,
            $availabilityMax,
            $participantIncluded
        );
        $expectedPackage->getTranslations()->set(
            'fr',
            new PackageTranslation(
                $expectedPackage,
                'fr',
                'foo',
                'bar',
                'barContent',
                'optional'
            )
        );
        $expectedPackage->getTranslations()->set(
            'en',
            new PackageTranslation(
                $expectedPackage,
                'en',
                'enfoo',
                'enbar',
                'enbarContent',
                'enoptional'
            )
        );
        $expectedPackage->getProductIncluded()->add(
            new Package\ProductIncluded($expectedPackage, $product, 2)
        );

        // Mock
        $pacakgeRepository = $this->prophesize(PackageRepositoryInterface::class);
        $pacakgeRepository->add($expectedPackage)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        // Handler
        $handler = new CreateHandler($pacakgeRepository->reveal(), $fileStorage->reveal());
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
        $participantIncluded = 2;
        $translations = [
            'fr' => [
                'title'              => 'foo',
                'descriptionTitle'   => 'bar',
                'descriptionContent' => 'barContent',
                'optionalPriceText'  => 'optional',
            ],
            'en' => [
                'title'              => 'enfoo',
                'descriptionTitle'   => 'enbar',
                'descriptionContent' => 'enbarContent',
                'optionalPriceText'  => 'enoptional',
            ],
        ];
        $product = new Product($event, 'product', '', 100, 2, 4, 3, 4, false, null);
        $product->getTranslations()->set(
            'fr',
            new ProductTranslation(
                $product,
                'fr',
                'foo',
                'bar',
                'optional'
            )
        );
        $product->getTranslations()->set(
            'en',
            new ProductTranslation(
                $product,
                'en',
                'enfoo',
                'enbar',
                'enoptional'
            )
        );

        $create                      = new Create($event);
        $create->name                = $name;
        $create->unitPrice           = $unitPrice;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->translations        = $translations;
        $create->file                = null;
        $create->participantIncluded = $participantIncluded;
        $create->productIncluded = [
            0 => [
                'product'  => $product,
                'quantity' => 2,
            ]
        ];
        $create->features = [
            0 => [
                'translations' => [
                    'fr' => ['title' => 'Titre', 'description' => 'Description'],
                    'en' => ['title' => 'Titre', 'description' => 'Description'],
                ]
            ]
        ];

        // Expected
        $expectedPackage = new Package(
            $event,
            $name,
            $image,
            $unitPrice,
            $availabilityCurrent,
            $availabilityMax,
            $participantIncluded
        );
        $expectedPackage->getTranslations()->set(
            'fr',
            new PackageTranslation(
                $expectedPackage,
                'fr',
                'foo',
                'bar',
                'barContent',
                'optional'
            )
        );
        $expectedPackage->getTranslations()->set(
            'en',
            new PackageTranslation(
                $expectedPackage,
                'en',
                'enfoo',
                'enbar',
                'enbarContent',
                'enoptional'
            )
        );
        $expectedPackage->getProductIncluded()->add(
            new Package\ProductIncluded($expectedPackage, $product, 2)
        );

        $feature = new Package\Feature($expectedPackage);
        $feature->getTranslations()->set(
            'fr',
            new Package\FeatureTranslation(
                $feature,
                'fr',
                'Titre',
                'Description'
            )
        );
        $feature->getTranslations()->set(
            'en',
            new Package\FeatureTranslation(
                $feature,
                'en',
                'Titre',
                'Description'
            )
        );
        $expectedPackage->getFeatures()->add($feature);

        // Mock
        $pacakgeRepository = $this->prophesize(PackageRepositoryInterface::class);
        $pacakgeRepository->add(Argument::that(function (Package $package) use ($expectedPackage) {
            return $package->getFeatures()->count() === $expectedPackage->getFeatures()->count();
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        // Handler
        $handler = new CreateHandler($pacakgeRepository->reveal(), $fileStorage->reveal());
        $handler->handle($create);
    }
}
