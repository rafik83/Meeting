<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Create;
use Proximum\Vimeet\Application\Command\Product\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductTranslation;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
        $quantityMin         = 2;
        $quantityMax         = 4;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $updatable           = true;
        $updatableUntil      = new \DateTime();
        $translations = [
            'fr' => [
                'title'             => 'foo',
                'description'       => 'bar',
                'optionalPriceText' => 'optional',
            ],
            'en' => [
                'title'             => 'enfoo',
                'description'       => 'enbar',
                'optionalPriceText' => 'enoptional',
            ],
        ];

        $create                      = new Create($event);
        $create->name                = $name;
        $create->unitPrice           = $unitPrice;
        $create->quantityMin         = $quantityMin;
        $create->quantityMax         = $quantityMax;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->updatable           = $updatable;
        $create->updatableUntil      = $updatableUntil;
        $create->translations        = $translations;
        $create->file                = null;


        // Expected
        $expectedProduct = new Product(
            $event,
            $name,
            $image,
            $unitPrice,
            $quantityMin,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            $updatableUntil
        );
        $expectedProduct->getTranslations()->set(
            'fr',
            new ProductTranslation(
                $expectedProduct,
                'fr',
                'foo',
                'bar',
                'optional'
            )
        );
        $expectedProduct->getTranslations()->set(
            'en',
            new ProductTranslation(
                $expectedProduct,
                'en',
                'enfoo',
                'enbar',
                'enoptional'
            )
        );

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->add($expectedProduct)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        // Handler
        $handler = new CreateHandler($productRepository->reveal(), $fileStorage->reveal());
        $handler->handle($create);
    }
}
