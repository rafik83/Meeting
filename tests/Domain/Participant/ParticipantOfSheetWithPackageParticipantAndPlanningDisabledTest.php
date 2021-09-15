<?php

namespace Proximum\Vimeet\Tests\Domain\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;

class ParticipantOfSheetWithPackageParticipantAndPlanningDisabledTest extends TestCase
{
    public function testHandle()
    {
        $package1 = $this->prophesize(Package::class);
        $package1->isParticipantAndPlanningEnabled()->willReturn(true);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getPackage()->willReturn($package1->reveal());
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getSheet()->willReturn($sheet1->reveal());

        $productParticipant = $this->prophesize(Product::class);
        $package2 = $this->prophesize(Package::class);
        $package2->isParticipantAndPlanningEnabled()->willReturn(false);
        $package2->getFirstProductParticipant()->shouldBeCalled()->willReturn($productParticipant->reveal());
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getPackage()->willReturn($package2->reveal());
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getSheet()->willReturn($sheet2->reveal());

        $participantProductSetter = $this->prophesize(ParticipantProductSetter::class);
        $participantProductSetter
            ->setProductOnParticipant($participant1->reveal(), $productParticipant->reveal())
            ->shouldNotBeCalled()
        ;
        $participantProductSetter
            ->setProductOnParticipant($participant2->reveal(), $productParticipant->reveal())
            ->shouldBeCalled()
        ;

        $handler = new ParticipantOfSheetWithPackageParticipantAndPlanningDisabled($participantProductSetter->reveal());
        $handler->handle($participant1->reveal());
        $handler->handle($participant2->reveal());
    }
}
