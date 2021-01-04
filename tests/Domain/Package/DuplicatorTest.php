<?php

namespace Proximum\Vimeet\Tests\Domain\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Duplicator;

class DuplicatorTest extends TestCase
{
    public function testDuplicatePackage()
    {
        $dateTime = new \DateTimeImmutable();

        $event = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'en']);

        $package = new Package($event->reveal(), 'Lorem ipsum', $dateTime);
        $package->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $package->translate('en', 'Plans', 'Participant and Planning', 'Options');

        $expectedPackage = new Package($event->reveal(), 'Duplicate package', $dateTime);
        $expectedPackage->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $expectedPackage->translate('en', 'Plans', 'Participant and Planning', 'Options');

        $duplicator = new Duplicator($dateTime);
        $result = $duplicator->duplicatePackage($package, 'Duplicate package');

        $this->assertEquals($expectedPackage, $result);
    }

    public function testDuplicatePackageWithCorrespondingProducts()
    {
        $dateTime = new \DateTimeImmutable();

        $plan1 = $this->prophesize(Product::class);
        $plan2 = $this->prophesize(Product::class);

        $participant = $this->prophesize(Product::class);
        $planning    = $this->prophesize(Product::class);
        $newParticipant = $this->prophesize(Product::class);
        $newPlanning    = $this->prophesize(Product::class);

        $newPlan1 = $this->prophesize(Product::class);
        $newPlan2 = $this->prophesize(Product::class);

        $newPlan1->isPlan()->willReturn(true);
        $newPlan2->isPlan()->willReturn(true);

        $option1 = $this->prophesize(Product::class);
        $option2 = $this->prophesize(Product::class);
        $option3 = $this->prophesize(Product::class);

        $newOption1 = $this->prophesize(Product::class);
        $newOption2 = $this->prophesize(Product::class);
        $newOption3 = $this->prophesize(Product::class);
        $newOption1->isOption()->willReturn(true);
        $newOption2->isOption()->willReturn(true);
        $newOption3->isOption()->willReturn(true);

        $plan1->getId()->willReturn(5);
        $plan2->getId()->willReturn(6);
        $participant->getId()->willReturn(11);
        $planning->getId()->willReturn(21);
        $option1->getId()->willReturn(31);
        $option2->getId()->willReturn(32);
        $option3->getId()->willReturn(33);

        $plan1->isPlan()->willReturn(true);
        $plan2->isPlan()->willReturn(true);
        $participant->isPlan()->willReturn(false);
        $planning->isPlan()->willReturn(false);
        $option1->isPlan()->willReturn(false);
        $option2->isPlan()->willReturn(false);
        $option3->isPlan()->willReturn(false);
        $plan1->isOption()->willReturn(false);
        $plan2->isOption()->willReturn(false);
        $participant->isOption()->willReturn(false);
        $planning->isOption()->willReturn(false);
        $option1->isOption()->willReturn(true);
        $option2->isOption()->willReturn(true);
        $option3->isOption()->willReturn(true);
        $newParticipant->isParticipant()->willReturn(true);
        $participant->isParticipant()->willReturn(true);

        $event    = $this->prophesize(Event::class);
        $newEvent = $this->prophesize(Event::class);
        $newEvent->getLocales()->willReturn(['fr', 'en', 'de']);

        $package = new Package($event->reveal(), 'Lorem ipsum', $dateTime);
        $package->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $package->translate('en', 'Plans', 'Participant and Planning', 'Options');
        $package->enable(true, false, true);
        $package->setParticipantWithPlanning(true);

        $package->setPlans([
            $plan1->reveal(),
            $plan2->reveal(),
        ]);

        $package->setParticipants([$participant->reveal()]);
        $package->setPlanning($planning->reveal());

        $packageGroup1 = new PackageGroup($package, 1);
        $packageGroup2 = new PackageGroup($package, 2);

        $packageGroup1->translate('fr', 'toto');
        $packageGroup1->translate('en', 'tata');
        $packageGroup2->translate('fr', 'titi');
        $packageGroup2->translate('en', 'tutu');
        $packageGroup1->setOptions([$option1->reveal(), $option2->reveal()]);
        $packageGroup2->setOptions([$option3->reveal()]);
        $package->setGroupsModel([$packageGroup1, $packageGroup2]);

        $products = [
            5  => $newPlan1->reveal(),
            6  => $newPlan2->reveal(),
            11 => $newParticipant->reveal(),
            21 => $newPlanning->reveal(),
            31 => $newOption1->reveal(),
            32 => $newOption2->reveal(),
            33 => $newOption3->reveal(),
        ];

        $expectedPackage = new Package($newEvent->reveal(), 'Lorem ipsum', $dateTime);
        $expectedPackage->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $expectedPackage->translate('en', 'Plans', 'Participant and Planning', 'Options');
        $expectedPackage->translate('de', '', '', '');
        $expectedPackage->enable(true, false, true);
        $expectedPackage->setParticipantWithPlanning(true);

        $expectedPackage->setPlans([
            $newPlan1->reveal(),
            $newPlan2->reveal(),
        ]);
        $expectedPackage->setParticipants([$newParticipant->reveal()]);
        $expectedPackage->setPlanning($newPlanning->reveal());
        $newPackageGroup1 = new PackageGroup($expectedPackage, 1);
        $newPackageGroup2 = new PackageGroup($expectedPackage, 2);
        $newPackageGroup1->translate('fr', 'toto');
        $newPackageGroup1->translate('en', 'tata');
        $newPackageGroup1->translate('de', '');
        $newPackageGroup2->translate('fr', 'titi');
        $newPackageGroup2->translate('en', 'tutu');
        $newPackageGroup2->translate('de', '');
        $newPackageGroup1->setOptions([$newOption1->reveal(), $newOption2->reveal()]);
        $newPackageGroup2->setOptions([$newOption3->reveal()]);
        $expectedPackage->setGroupsModel([$newPackageGroup1, $newPackageGroup2]);

        $duplicator = new Duplicator($dateTime);
        $result = $duplicator->duplicatePackageWithCorrespondingProducts($newEvent->reveal(), $package, $products);

        $this->assertEquals($expectedPackage, $result);
    }
}
