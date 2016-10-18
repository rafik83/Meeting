<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product\Import;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Import\Import;
use Proximum\Vimeet\Application\Command\Product\Import\ImportHandler;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ImportHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event        = EventFactory::createEvent();
        $currentEvent = EventFactory::createEvent();
        $dateTime     = new \DateTime();

        $product = new Product(
            $event,
            Product::TYPE_OPTION,
            'name',
            'image',
            10,
            10,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );
        $product2 = new Product(
            $event,
            Product::TYPE_PARTICIPANT,
            'participant',
            'image3',
            10,
            null,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );

        $expectedProduct = new Product(
            $currentEvent,
            Product::TYPE_OPTION,
            'name',
            'image2',
            10,
            10,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );
        $expectedProduct2 = new Product(
            $currentEvent,
            Product::TYPE_PARTICIPANT,
            'participant',
            'image4',
            10,
            null,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );
        $expectedProduct->translate('fr', '', '', '', '', '');
        $expectedProduct->translate('en', '', '', '', '', '');
        $expectedProduct2->translate('fr', '', '', '', '', '');
        $expectedProduct2->translate('en', '', '', '', '', '');

        $package         = new Package($event, '', $dateTime);
        $expectedPackage = new Package($currentEvent, '', $dateTime);
        $expectedPackage->translate('fr', '', '', '');
        $expectedPackage->translate('en', '', '', '');

        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->findByEvent($event)->shouldBeCalled()->willReturn([$product, $product2]);

        $productRepository->add(Argument::that(function (Product $product) use ($expectedProduct, $expectedProduct2) {
            if (($product->getType() === $expectedProduct->getType() || $product->getType() === $expectedProduct2->getType())
                && ($product->getUnitPrice() === $expectedProduct->getUnitPrice() || $product->getUnitPrice() === $expectedProduct2->getUnitPrice())
                && ($product->getQuantityMax() === $expectedProduct->getQuantityMax() || $product->getQuantityMax() === $expectedProduct2->getQuantityMax())
            ) {
                return true;
            }

            return false;
        }))->shouldBeCalled();

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $packageRepository->findByEvent($event)->shouldBeCalled()->willReturn([$package]);
        $packageRepository->add($expectedPackage)->shouldBeCalled();

        $fileStorage       = $this->prophesize(FileStorageInterface::class);
        $fileStorage->copyAndRename('image')->shouldBeCalled()->willReturn('image2');
        $fileStorage->copyAndRename('image3')->shouldBeCalled()->willReturn('image4');

        $import = new Import($currentEvent);
        $import->event = $event;

        $handler = new ImportHandler(
            $productRepository->reveal(),
            $packageRepository->reveal(),
            $fileStorage->reveal(),
            $dateTime
        );
        $handler->handle($import);
    }
}
