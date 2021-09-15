<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\EnableBadgeForParticipantDateAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;

class EnableBadgeForParticipantDateAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessWithNoDate(): void
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);

        $configuration = $this->prophesize(Event\Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->getEnableBadgeForParticipantDate()->shouldBeCalled()->willReturn(null);

        $accessChecker = new EnableBadgeForParticipantDateAccessChecker($dateTime);

        $this->assertFalse($accessChecker->allowedToAccess($event->reveal()));
    }

    public function testAllowedToAccessWithInTheFutureDate(): void
    {
        $dateTime = new \DateTime('2018-10-10 10:00:00.000');
        $enableBadgeForParticipantDate = new \DateTime('2018-12-12 10:00:00.000');

        $event = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration
            ->getEnableBadgeForParticipantDate()
            ->shouldBeCalled()
            ->willReturn($enableBadgeForParticipantDate)
        ;

        $accessChecker = new EnableBadgeForParticipantDateAccessChecker($dateTime);

        $this->assertFalse($accessChecker->allowedToAccess($event->reveal()));
    }

    public function testAllowedToAccessWithPassedDate(): void
    {
        $dateTime = new \DateTime('2018-10-10 10:00:00.000');
        $enableBadgeForParticipantDate = new \DateTime('2018-08-08 10:00:00.000');

        $event = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration
            ->getEnableBadgeForParticipantDate()
            ->shouldBeCalled()
            ->willReturn($enableBadgeForParticipantDate)
        ;

        $accessChecker = new EnableBadgeForParticipantDateAccessChecker($dateTime);

        $this->assertTrue($accessChecker->allowedToAccess($event->reveal()));
    }
}
