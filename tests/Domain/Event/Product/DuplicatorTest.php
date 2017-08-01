<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Event\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Event\Product\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventDuplicated = EventFactory::createEvent('event duplicated');
        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en',],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );

        $result          = [ProductFactory::create($eventDuplicated)];
        $expectedProduct = ProductFactory::create($event);
        $expectedProduct->updateOption(
            $expectedProduct->getName(),
            'new image',
            $expectedProduct->getQuantityMax(),
            $expectedProduct->getAvailabilityCurrent(),
            $expectedProduct->getAvailabilityMax(),
            $expectedProduct->isUpdatable(),
            $expectedProduct->getUnitPrice(),
            $expectedProduct->getDeletableUntil(),
            $expectedProduct->isSubjectedToValidation(),
            $expectedProduct->getBuyableUntil()
        );

        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $fileStorage       = $this->prophesize(FileStorageInterface::class);

        $fileStorage->copyAndRename('image')->shouldBeCalled()->willReturn('new image');
        $productRepository->findByEvent($eventDuplicated)->shouldBeCalled()->willReturn($result);
        $productRepository->add($expectedProduct)->shouldBeCalled();

        (new Duplicator($productRepository->reveal(), $fileStorage->reveal()))->duplicate($event);
    }
}
