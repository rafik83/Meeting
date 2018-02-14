<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Model;

use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public function testSetGroups()
    {
        // Context
        $event    = EventFactory::createEvent();
        $dateTime = new \DateTime();

        $package = new Package($event, 'my event', $dateTime);

        $option1 = Product::createOption($event, 'option 1', 'option1.jpg', 100, 20, 1, 1, 1, true);
        $option2 = Product::createOption($event, 'option 2', 'option2.jpg', 100, 20, 1, 1, 1, true);
        $option3 = Product::createOption($event, 'option 3', 'option3.jpg', 100, 20, 1, 1, 1, true);
        $option4 = Product::createOption($event, 'option 4', 'option4.jpg', 100, 20, 1, 1, 1, true);
        $option5 = Product::createOption($event, 'option 5', 'option5.jpg', 100, 20, 1, 1, 1, true);

        $package->setGroups([
            [$option1, $option3],
            [$option2, $option5, $option4]
        ], [
            ['fr' => 'Group 1'],
            ['fr' => 'Group 2'],
        ]);

        // Expected
        $groups = [
            (new PackageGroup($package, 0))->translate('fr', 'Group 1')->setOptions([$option1, $option3]),
            (new PackageGroup($package, 1))->translate('fr', 'Group 2')->setOptions([$option2, $option5, $option4]),
        ];

        $this->assertEquals($groups, $package->getGroups());
    }
}
