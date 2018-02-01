<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product\Plan;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlan;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlanHandler;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

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

        $updateCommand = new UpdatePlan($plan);
        $updateCommand->name = 'name plan modify';
        $updateCommand->unitPrice = 200;
        $updateCommand->vat = 19;

        // Mock
        $pacakgeRepository = $this->prophesize(ProductRepositoryInterface::class);
        $pacakgeRepository->update(Argument::that(function (Product $givenPlan) use ($expectedPlan) {
            return $givenPlan->getType() === $expectedPlan->getType()
                && $givenPlan->getVat() === 19
                && $givenPlan->getUnitPrice() === 200
                && $givenPlan->getAvailabilityCurrent() === $expectedPlan->getAvailabilityCurrent()
                && $givenPlan->getAvailabilityMax() === $expectedPlan->getAvailabilityMax()
                && $givenPlan->getName() === $expectedPlan->getName()
            ;
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($plan)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new UpdatePlanHandler(
            $pacakgeRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($updateCommand);
    }
}
