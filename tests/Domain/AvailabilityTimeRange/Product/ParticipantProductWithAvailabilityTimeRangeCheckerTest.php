<?php

namespace Proximum\Vimeet\Tests\Domain\AvailabilityTimeRange\Product;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\AvailabilityTimeRange\Product\ParticipantProductWithAvailabilityTimeRangeChecker;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Time\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class ParticipantProductWithAvailabilityTimeRangeCheckerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $overlappedTimeRangeMerger;

    /** @var ObjectProphecy */
    private $overlappedTimeRangeTruncater;

    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $product;

    /** @var ObjectProphecy */
    private $package;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getSheet()->willReturn($this->sheet->reveal());
        $this->participant->getId()->willReturn(121);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->participant->getUser()->willReturn($this->user->reveal());
        $this->product = $this->prophesize(Product::class);
        $this->package = $this->prophesize(Package::class);
        $this->sheet->getPackage()->willReturn($this->package->reveal());
        $this->package->isPassable()->willReturn(true);

        $this->overlappedTimeRangeMerger = $this->prophesize(OverlappedTimeRangeMerger::class);
        $this->overlappedTimeRangeTruncater = $this->prophesize(OverlappedTimeRangeTruncater::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
    }

    public function testNoPreviousProduct(): void
    {
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $checker = new ParticipantProductWithAvailabilityTimeRangeChecker(
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->participantRepository->reveal()
        );

        $result = $checker->canSetProduct($this->participant->reveal(), $this->product->reveal());

        $this->assertTrue($result);
    }

    public function testSameProduct(): void
    {
        $this->product->getId()->willReturn(12);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($this->product->reveal());

        $checker = new ParticipantProductWithAvailabilityTimeRangeChecker(
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->participantRepository->reveal()
        );

        $result = $checker->canSetProduct($this->participant->reveal(), $this->product->reveal());

        $this->assertTrue($result);
    }

    public function testNoPreviousTimeRange(): void
    {
        $this->product->getId()->willReturn(12);
        $oldProduct = $this->prophesize(Product::class);
        $oldProduct->getId()->willReturn(14);
        $oldProduct->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn([]);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($oldProduct->reveal());

        $checker = new ParticipantProductWithAvailabilityTimeRangeChecker(
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->participantRepository->reveal()
        );

        $result = $checker->canSetProduct($this->participant->reveal(), $this->product->reveal());

        $this->assertTrue($result);
    }

    public function testPackageNotPassable(): void
    {
        $this->product->getId()->willReturn(12);
        $oldProduct = $this->prophesize(Product::class);
        $oldProduct->getId()->willReturn(14);

        $availability1 = $this->prophesize(AvailabilityTimeRange::class);
        $oldProduct->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn([$availability1->reveal()]);
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($oldProduct->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $package2 = $this->prophesize(Package::class);
        $participant2->getSheet()->willReturn($sheet2->reveal());
        $participant2->getId()->willReturn(122);
        $sheet2->getPackage()->shouldBeCalled()->willReturn($package2->reveal());
        $package2->isPassable()->shouldBeCalled()->willReturn(false);

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->participant->reveal(), $participant2->reveal()])
        ;

        $checker = new ParticipantProductWithAvailabilityTimeRangeChecker(
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->participantRepository->reveal()
        );

        $result = $checker->canSetProduct($this->participant->reveal(), $this->product->reveal());

        $this->assertTrue($result);
    }

    public function testCanNotSet(): void
    {
        $this->product->getId()->willReturn(12);
        $oldProduct = $this->prophesize(Product::class);
        $oldProduct->getId()->willReturn(14);

        $availability1 = $this->prophesize(AvailabilityTimeRange::class);
        $availability2 = $this->prophesize(AvailabilityTimeRange::class);
        $availability3 = $this->prophesize(AvailabilityTimeRange::class);
        $availability4 = $this->prophesize(AvailabilityTimeRange::class);
        $availability5 = $this->prophesize(AvailabilityTimeRange::class);
        $availability6 = $this->prophesize(AvailabilityTimeRange::class);

        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $begin2 = new \DateTime('2017-10-10 14:00:00.000');
        $begin3 = new \DateTime('2017-10-11 10:00:00.000');
        $begin4 = new \DateTime('2017-10-11 14:00:00.000');
        $begin5 = new \DateTime('2017-10-10 10:00:00.000');
        $begin6 = new \DateTime('2017-10-12 10:00:00.000');
        $end1   = new \DateTime('2017-10-10 13:00:00.000');
        $end2   = new \DateTime('2017-10-10 18:00:00.000');
        $end3   = new \DateTime('2017-10-11 13:00:00.000');
        $end4   = new \DateTime('2017-10-11 18:00:00.000');
        $end5   = new \DateTime('2017-10-10 18:00:00.000');
        $end6   = new \DateTime('2017-10-12 18:00:00.000');

        $availability1->getBegin()->willReturn($begin1);
        $availability2->getBegin()->willReturn($begin2);
        $availability3->getBegin()->willReturn($begin3);
        $availability4->getBegin()->willReturn($begin4);
        $availability5->getBegin()->willReturn($begin5);
        $availability6->getBegin()->willReturn($begin6);
        $availability1->getEnd()->willReturn($end1);
        $availability2->getEnd()->willReturn($end2);
        $availability3->getEnd()->willReturn($end3);
        $availability4->getEnd()->willReturn($end4);
        $availability5->getEnd()->willReturn($end5);
        $availability6->getEnd()->willReturn($end6);

        $availability1->getId()->willReturn(221);
        $availability2->getId()->willReturn(222);
        $availability3->getId()->willReturn(223);
        $availability4->getId()->willReturn(224);
        $availability5->getId()->willReturn(225);
        $availability6->getId()->willReturn(226);
        $oldProduct->getAvailabilityTimeRanges()
            ->shouldBeCalled()
            ->willReturn([
                $availability1->reveal(),
                $availability2->reveal(),
                $availability3->reveal(),
            ])
        ;
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($oldProduct->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $package2 = $this->prophesize(Package::class);
        $participant2->getSheet()->willReturn($sheet2->reveal());
        $participant2->getId()->willReturn(122);
        $sheet2->getPackage()->shouldBeCalled()->willReturn($package2->reveal());
        $package2->isPassable()->shouldBeCalled()->willReturn(true);
        $otherProduct = $this->prophesize(Product::class);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn($otherProduct->reveal());
        $otherProduct->getAvailabilityTimeRanges()->willReturn([
            $availability3->reveal(),
            $availability4->reveal(),
        ]);
        $this->product->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn([
            $availability2->reveal(),
            $availability4->reveal(),
            $availability5->reveal(),
            $availability6->reveal()
        ]);

        $timeRangeView2 = new TimeRangeView($begin2, $end2);
        $timeRangeView3 = new TimeRangeView($begin3, $end3);
        $timeRangeView4 = new TimeRangeView($begin4, $end4);
        $timeRangeView5 = new TimeRangeView($begin5, $end5);
        $timeRangeView6 = new TimeRangeView($begin6, $end6);
        $timeRangeNotAccessibleView = new TimeRangeNotAccessibleView($begin1, $end1);
        $timeRangeViews = [
            222 => $timeRangeView2,
            224 => $timeRangeView4,
            225 => $timeRangeView5,
            226 => $timeRangeView6,
            223 => $timeRangeView3,
        ];

        $this->overlappedTimeRangeMerger->merge($timeRangeViews)->shouldBeCalled()->willReturn($timeRangeViews);
        $this->overlappedTimeRangeMerger->merge([$timeRangeNotAccessibleView])->shouldBeCalled()->willReturn([$timeRangeNotAccessibleView]);
        $this->overlappedTimeRangeTruncater
            ->truncate($timeRangeNotAccessibleView, $timeRangeViews)
            ->shouldBeCalled()
            ->willReturn([$timeRangeNotAccessibleView])
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->participant->reveal(), $participant2->reveal()])
        ;

        $this->participantRepository
            ->getAvailableParticipants([$this->participant->reveal()], $begin1, $end1, null, null, true)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $checker = new ParticipantProductWithAvailabilityTimeRangeChecker(
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->participantRepository->reveal()
        );

        $result = $checker->canSetProduct($this->participant->reveal(), $this->product->reveal());

        $this->assertFalse($result);
    }

    public function testCanSet(): void
    {
        $this->product->getId()->willReturn(12);
        $oldProduct = $this->prophesize(Product::class);
        $oldProduct->getId()->willReturn(14);

        $availability1 = $this->prophesize(AvailabilityTimeRange::class);
        $availability2 = $this->prophesize(AvailabilityTimeRange::class);
        $availability3 = $this->prophesize(AvailabilityTimeRange::class);
        $availability4 = $this->prophesize(AvailabilityTimeRange::class);
        $availability5 = $this->prophesize(AvailabilityTimeRange::class);
        $availability6 = $this->prophesize(AvailabilityTimeRange::class);

        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $begin2 = new \DateTime('2017-10-10 14:00:00.000');
        $begin3 = new \DateTime('2017-10-11 10:00:00.000');
        $begin4 = new \DateTime('2017-10-11 14:00:00.000');
        $begin5 = new \DateTime('2017-10-10 10:00:00.000');
        $begin6 = new \DateTime('2017-10-12 10:00:00.000');
        $end1   = new \DateTime('2017-10-10 13:00:00.000');
        $end2   = new \DateTime('2017-10-10 18:00:00.000');
        $end3   = new \DateTime('2017-10-11 13:00:00.000');
        $end4   = new \DateTime('2017-10-11 18:00:00.000');
        $end5   = new \DateTime('2017-10-10 18:00:00.000');
        $end6   = new \DateTime('2017-10-12 18:00:00.000');

        $availability1->getBegin()->willReturn($begin1);
        $availability2->getBegin()->willReturn($begin2);
        $availability3->getBegin()->willReturn($begin3);
        $availability4->getBegin()->willReturn($begin4);
        $availability5->getBegin()->willReturn($begin5);
        $availability6->getBegin()->willReturn($begin6);
        $availability1->getEnd()->willReturn($end1);
        $availability2->getEnd()->willReturn($end2);
        $availability3->getEnd()->willReturn($end3);
        $availability4->getEnd()->willReturn($end4);
        $availability5->getEnd()->willReturn($end5);
        $availability6->getEnd()->willReturn($end6);

        $availability1->getId()->willReturn(221);
        $availability2->getId()->willReturn(222);
        $availability3->getId()->willReturn(223);
        $availability4->getId()->willReturn(224);
        $availability5->getId()->willReturn(225);
        $availability6->getId()->willReturn(226);
        $oldProduct->getAvailabilityTimeRanges()
            ->shouldBeCalled()
            ->willReturn([
                $availability1->reveal(),
                $availability2->reveal(),
                $availability3->reveal(),
            ])
        ;
        $this->participant->getParticipantProduct()->shouldBeCalled()->willReturn($oldProduct->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $package2 = $this->prophesize(Package::class);
        $participant2->getSheet()->willReturn($sheet2->reveal());
        $participant2->getId()->willReturn(122);
        $sheet2->getPackage()->shouldBeCalled()->willReturn($package2->reveal());
        $package2->isPassable()->shouldBeCalled()->willReturn(true);
        $otherProduct = $this->prophesize(Product::class);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn($otherProduct->reveal());
        $otherProduct->getAvailabilityTimeRanges()->willReturn([
            $availability3->reveal(),
            $availability4->reveal(),
        ]);
        $this->product->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn([
            $availability2->reveal(),
            $availability4->reveal(),
            $availability5->reveal(),
            $availability6->reveal()
        ]);

        $timeRangeView2 = new TimeRangeView($begin2, $end2);
        $timeRangeView3 = new TimeRangeView($begin3, $end3);
        $timeRangeView4 = new TimeRangeView($begin4, $end4);
        $timeRangeView5 = new TimeRangeView($begin5, $end5);
        $timeRangeView6 = new TimeRangeView($begin6, $end6);
        $timeRangeNotAccessibleView = new TimeRangeNotAccessibleView($begin1, $end1);
        $timeRangeViews = [
            222 => $timeRangeView2,
            224 => $timeRangeView4,
            225 => $timeRangeView5,
            226 => $timeRangeView6,
            223 => $timeRangeView3,
        ];

        $this->overlappedTimeRangeMerger->merge($timeRangeViews)->shouldBeCalled()->willReturn($timeRangeViews);
        $this->overlappedTimeRangeMerger->merge([$timeRangeNotAccessibleView])->shouldBeCalled()->willReturn([$timeRangeNotAccessibleView]);
        $this->overlappedTimeRangeTruncater
            ->truncate($timeRangeNotAccessibleView, $timeRangeViews)
            ->shouldBeCalled()
            ->willReturn([$timeRangeNotAccessibleView])
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->participant->reveal(), $participant2->reveal()])
        ;

        $this->participantRepository
            ->getAvailableParticipants([$this->participant->reveal()], $begin1, $end1, null, null, true)
            ->shouldBeCalled()
            ->willReturn([$this->participant->reveal()])
        ;

        $checker = new ParticipantProductWithAvailabilityTimeRangeChecker(
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->participantRepository->reveal()
        );

        $result = $checker->canSetProduct($this->participant->reveal(), $this->product->reveal());

        $this->assertTrue($result);
    }
}
