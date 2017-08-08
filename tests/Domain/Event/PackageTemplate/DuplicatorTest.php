<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Event\PackageTemplate;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Package\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $date = new \DateTime();
        $eventDuplicated = EventFactory::createEvent('event duplicated');
        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );

        $option = ProductFactory::create($eventDuplicated);
        $reflection = new \ReflectionClass(Product::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($option, 1);

        $plan = ProductFactory::create($eventDuplicated, Product::TYPE_PLAN);
        $reflection = new \ReflectionClass(Product::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($plan, 3);

        $planning = ProductFactory::create($eventDuplicated, Product::TYPE_PLANNING);
        $reflection = new \ReflectionClass(Product::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($planning, 5);

        $participant = ProductFactory::create($eventDuplicated, Product::TYPE_PARTICIPANT);
        $reflection = new \ReflectionClass(Product::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 7);

        $package = new Package($eventDuplicated, 'package title', $date);
        $clonedPackage = new Package($event, 'package title', $date);
        $reflection = new \ReflectionClass(Package::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($package, 2);

        $package->setGroups([[$option]], [['fr' => 'label option']]);
        $package->setPlans([$plan]);
        $package->setPlanning($planning);
        $package->setParticipant($participant);

        $clonedPackage->setGroups([[$option]], [['fr' => 'label option']]);
        $clonedPackage->setPlans([$plan]);
        $clonedPackage->setPlanning($planning);
        $clonedPackage->setParticipant($participant);

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $packageRepository->add(Argument::that(function (Package $givenPackage) use ($package) {
            return $givenPackage;
        }))->shouldBeCalled();

        $packageRepository
            ->findByEvent($eventDuplicated)
            ->shouldBeCalled()
            ->willReturn([$package])
        ;

        $duplicatorDataStorage = new DuplicatorDataStorage();
        $duplicatorDataStorage->products = [
            1 => $option,
            3 => $plan,
            5 => $planning,
            7 => $participant
        ];

        $duplicator = new Duplicator($packageRepository->reveal(), $date);
        $result = $duplicator->duplicate($event, $duplicatorDataStorage);

        /** @var Package $newPackageResult */
        $newPackageResult = $result->packageTemplates[2];
        $resultOption = $newPackageResult->getGroups()[0]->getOptions()[0];
        $resultPlan = $newPackageResult->getPlans()[0];

        $this->assertEquals($option, $resultOption);
        $this->assertEquals($plan, $resultPlan);
        $this->assertEquals($planning, $newPackageResult->getPlanning());
        $this->assertEquals($participant, $newPackageResult->getParticipant());
    }
}
