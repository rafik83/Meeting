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
use Proximum\Vimeet\Application\Command\Product\Option\CreateOption;
use Proximum\Vimeet\Application\Command\Product\Option\CreateOptionHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreateOptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en'], 'fr');

        $name                = 'Name';
        $image               = 'Image';
        $unitPrice           = 100;
        $quantityMax         = 4;
        $availabilityCurrent = 10;
        $availabilityMax     = 50;
        $updatable           = true;
        $updatableUntil      = new \DateTime();
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
        $create->unitPrice           = $unitPrice;
        $create->quantityMax         = $quantityMax;
        $create->availabilityCurrent = $availabilityCurrent;
        $create->availabilityMax     = $availabilityMax;
        $create->updatable           = $updatable;
        $create->updatableUntil      = $updatableUntil;
        $create->translations        = $translations;
        $create->file                = null;


        // Expected
        $expectedProduct = Product::createOption(
            $event,
            $name,
            $image,
            $unitPrice,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            $updatableUntil
        );
        $expectedProduct->translate('fr', 'foo', null, 'bar', 'optional', '');
        $expectedProduct->translate('en', 'enfoo', null, 'enbar', 'enoptional', '');

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->add($expectedProduct)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        // Handler
        $handler = new CreateOptionHandler($productRepository->reveal(), $fileStorage->reveal());
        $handler->handle($create);
    }
}
