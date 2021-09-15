<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PackageTest extends TestCase
{
    public function testSetGroups()
    {
        // Context
        $event    = EventFactory::createEvent();
        $dateTime = new \DateTime();

        $package = new Package($event, 'my event', $dateTime);

        $option1 = $this->prophesize(Product::class);
        $option1->getId()->willReturn(1);
        $option1->isOption()->willReturn(true);

        $option2 = $this->prophesize(Product::class);
        $option2->getId()->willReturn(2);
        $option2->isOption()->willReturn(true);

        $option3 = $this->prophesize(Product::class);
        $option3->getId()->willReturn(3);
        $option3->isOption()->willReturn(true);

        $option4 = $this->prophesize(Product::class);
        $option4->getId()->willReturn(4);
        $option4->isOption()->willReturn(true);

        $option5 = $this->prophesize(Product::class);
        $option5->getId()->willReturn(5);
        $option5->isOption()->willReturn(true);

        $package->setGroups([
            [
                $option1->reveal(),
                $option3->reveal()
            ],
            [
                $option2->reveal(),
                $option5->reveal(),
                $option4->reveal(),
                // duplicated options
                $option1->reveal(),
                $option2->reveal()
            ],
        ], [
            ['fr' => 'Group 1'],
            ['fr' => 'Group 2'],
        ]);

        // Expected
        $groups = [
            (new PackageGroup($package, 0))->translate('fr', 'Group 1')->setOptions([$option1->reveal(), $option3->reveal()]),
            (new PackageGroup($package, 1))->translate('fr', 'Group 2')->setOptions([$option2->reveal(), $option5->reveal(), $option4->reveal()]),
        ];

        $this->assertEquals($groups, $package->getGroups());
    }

    public function testGetAvailablePlans()
    {
        $event = EventFactory::createEvent();
        $dateTime = new \DateTime();
        $package = new Package($event, 'My event', $dateTime);

        $plan1 = Product::createPlan($event, 'Plan 1', null, 100, 20, 1, 10); // available
        $plan2 = Product::createPlan($event, 'Plan 2', null, 99, 20, 0, 10); // out of stock
        $plan3 = Product::createPlan($event, 'Plan 3', null, 199, 20, 0, 0); // no stock, so available
        $package->setPlans([$plan1, $plan2, $plan3]);

        $this->assertSame([$plan1, $plan3], $package->getAvailablePlans());
    }

    public function testHasAtLeastOneProduct()
    {
        $event = EventFactory::createEvent();
        $dateTime = new \DateTime();
        $package = new Package($event, 'My event', $dateTime);

        $option1 = $this->prophesize(Product::class);
        $option1->getId()->willReturn(1);
        $option1->isOption()->willReturn(true);

        $option2 = $this->prophesize(Product::class);
        $option2->getId()->willReturn(2);
        $option2->isOption()->willReturn(true);

        $package->setGroupsOptions([[$option1->reveal(), $option2->reveal()]]);

        $this->assertFalse($package->hasAtLeastOneProduct([3, 4]));
        $this->assertTrue($package->hasAtLeastOneProduct([3, 2]));
    }
}
