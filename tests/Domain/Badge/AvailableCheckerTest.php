<?php

namespace Proximum\Vimeet\Tests\Domain\Badge;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Badge\AvailableChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\EnableBadgeForParticipantDateAccessChecker;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;

class AvailableCheckerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $enableBadgeForParticipantDateAccessChecker;

    /** @var ObjectProphecy */
    private $badgeRepository;

    /** @var ObjectProphecy */
    private $hasRemainingToPay;


    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);
        $this->event = $this->prophesize(Event::class);

        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());

        $this->enableBadgeForParticipantDateAccessChecker = $this->prophesize(EnableBadgeForParticipantDateAccessChecker::class);
        $this->badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $this->hasRemainingToPay = $this->prophesize(HasRemainingToPay::class);
    }

    public function testIsSatisfiedByDateNotPassed(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertFalse($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsSatisfiedBadgeNoActivated(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $badge = $this->prophesize(Badge::class);
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn($badge)
        ;

        $badge->isActivated()->shouldBeCalled()->willReturn(false);

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertFalse($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsSatisfiedBadgeConditionedWithPackage(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $badge = $this->prophesize(Badge::class);
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn($badge)
        ;

        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isConditioned()->shouldBeCalled()->willReturn(true);
        $badge->isConditionedByPackage()->shouldBeCalled()->willReturn(true);

        $this->hasRemainingToPay->isSatisfiedBy($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertFalse($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsSatisfiedBadgeConditionedWithPackageAndState(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $badge = $this->prophesize(Badge::class);
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn($badge)
        ;

        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isConditioned()->shouldBeCalled()->willReturn(true);
        $badge->isConditionedByPackage()->shouldBeCalled()->willReturn(true);

        $this->hasRemainingToPay->isSatisfiedBy($this->sheet->reveal())->shouldBeCalled()->willReturn(false);

        $badge->getConditionedByStates()->shouldBeCalled()->willReturn(['validated']);
        $this->sheet->getState()->shouldBeCalled()->willReturn('pending');

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertFalse($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }


    public function testIsSatisfiedBadgeConditionedWithState(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $badge = $this->prophesize(Badge::class);
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn($badge)
        ;

        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isConditioned()->shouldBeCalled()->willReturn(true);
        $badge->isConditionedByPackage()->shouldBeCalled()->willReturn(false);

        $this->hasRemainingToPay->isSatisfiedBy($this->sheet->reveal())->shouldNotBeCalled();

        $badge->getConditionedByStates()->shouldBeCalled()->willReturn(['validated']);
        $this->sheet->getState()->shouldBeCalled()->willReturn('pending');

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertFalse($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsSatisfiedBadgeConditionedNotForPackageAndState(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $badge = $this->prophesize(Badge::class);
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn($badge)
        ;

        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isConditioned()->shouldBeCalled()->willReturn(true);
        $badge->isConditionedByPackage()->shouldBeCalled()->willReturn(false);

        $this->hasRemainingToPay->isSatisfiedBy($this->sheet->reveal())->shouldNotBeCalled();
        $badge->getConditionedByStates()->shouldBeCalled()->willReturn([]);
        $this->sheet->getState()->shouldNotBeCalled();

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertTrue($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsSatisfiedBadgeNotConditioned(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $badge = $this->prophesize(Badge::class);
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn($badge)
        ;

        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isConditioned()->shouldBeCalled()->willReturn(false);

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertTrue($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsSatisfiedWithoutBadge(): void
    {
        $this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $availableChecker = new AvailableChecker(
            $this->enableBadgeForParticipantDateAccessChecker->reveal(),
            $this->badgeRepository->reveal(),
            $this->hasRemainingToPay->reveal()
        );

        $this->assertTrue($availableChecker->isSatisfiedBy($this->sheet->reveal()));
    }
}
