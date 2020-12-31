<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;

class AddParticipantCheckerTest extends TestCase
{
    /** @var AddParticipantChecker */
    private $addParticipantChecker;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $package;

    public function setUp()
    {
        $this->addParticipantChecker = new AddParticipantChecker();
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);
        $this->package = $this->prophesize(Package::class);

        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->type->getPackage()->willReturn($this->package->reveal());
    }

    public function testCanAddParticipantFalseAsMaxParticipantReachAndParticipantStepDisabled()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(4);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(true);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertFalse($result);
    }

    public function testCanAddParticipantTrueAsMaxParticipantInfAndParticipantStepDisable()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(null);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(false);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertTrue($result);
    }

    public function testCanAddParticipantFalseAsMaxParticipantReach()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(4);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(true);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertFalse($result);
    }

    public function testCanAddParticipantFalseAsMaxQuantityParticipantProductReach()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(7);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(true);

        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct2 = $this->prophesize(Product::class);

        $this->package->getParticipants()->willReturn([$participantProduct1->reveal(), $participantProduct2->reveal()]);
        $participantProduct1->getQuantityMax()->willReturn(2);
        $participantProduct2->getQuantityMax()->willReturn(2);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertFalse($result);
    }

    public function testCanAddParticipantTrueWithInfiniteProduct()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(7);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(true);

        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct2 = $this->prophesize(Product::class);

        $this->package->getParticipants()->willReturn([$participantProduct1->reveal(), $participantProduct2->reveal()]);
        $participantProduct1->getQuantityMax()->willReturn(INF);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertTrue($result);
    }

    public function testCanAddParticipantTrueWithQuantityHigherThanNumberOfParticipants()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(7);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(true);

        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct2 = $this->prophesize(Product::class);

        $this->package->getParticipants()->willReturn([$participantProduct1->reveal(), $participantProduct2->reveal()]);
        $participantProduct1->getQuantityMax()->willReturn(2);
        $participantProduct2->getQuantityMax()->willReturn(8);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertTrue($result);
    }

    public function testCanAddParticipantTrue()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(7);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(true);

        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct2 = $this->prophesize(Product::class);

        $this->package->getParticipants()->willReturn([$participantProduct1->reveal(), $participantProduct2->reveal()]);
        $participantProduct1->getQuantityMax()->willReturn(2);
        $participantProduct2->getQuantityMax()->willReturn(3);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertTrue($result);
    }

    public function testCanAddParticipantTrueAsMaxParticipantAndParticipantStepDisabled()
    {
        $this->sheet->countParticipants()->willReturn(4);
        $this->package->getMaxParticipant()->willReturn(5);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(false);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertTrue($result);
    }

    public function testCanNotAddParticipantAsMaxParticipantAndParticipantStepDisabled()
    {
        $this->sheet->countParticipants()->willReturn(5);
        $this->package->getMaxParticipant()->willReturn(5);
        $this->package->isParticipantAndPlanningEnabled()->willReturn(false);

        $result = $this->addParticipantChecker->canAddParticipant($this->sheet->reveal());
        $this->assertFalse($result);
    }
}
