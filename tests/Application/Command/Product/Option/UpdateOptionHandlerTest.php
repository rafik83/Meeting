<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product\Option;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOptionHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateOptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $image = 'Image';
        $unitPrice = 100;
        $quantityMax = 4;
        $availabilityCurrent = 10;
        $availabilityMax = 50;
        $updatable = true;

        $option = Product::createOption(
            $event,
            $name,
            $image,
            $unitPrice,
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
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedOption->translate($locale, '', '', '', '', '');
        }

        // Command
        $updateOptionCommand = new UpdateOption($option);
        $updateOptionCommand->name = 'my option updated';

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update($expectedOption)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');
        
        // Handler
        $handler = new UpdateOptionHandler($productRepository->reveal(), $fileStorage->reveal());
        $handler->handle($updateOptionCommand);
    }
}
