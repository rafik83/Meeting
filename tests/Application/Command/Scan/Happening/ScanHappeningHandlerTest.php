<?php

namespace Proximum\Vimeet\Tests\Application\Command\Scan\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Scan\Happening\ScanHappening;
use Proximum\Vimeet\Application\Command\Scan\Happening\ScanHappeningHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;

class ScanHappeningHandlerTest extends TestCase
{
    public function testHandleWithAlreadyCheck()
    {
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $dateTime = new \DateTime();
        $scannedAt = new \DateTime();
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willReturn(12);

        $scanRepository
            ->hasScanForUserEventTypeAndObjectId(
                $user->reveal(),
                $event->reveal(),
                Type::TYPE_HAPPENING_ENTRANCE,
                12
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $scanRepository->add(Argument::any())->shouldNotBeCalled();

        $handler = new ScanHappeningHandler(
            $scanRepository->reveal(),
            $dateTime
        );

        $command = new ScanHappening(
            $event->reveal(),
            $user->reveal(),
            $happening->reveal(),
            $scannedAt
        );
        $handler->handle($command);
    }

    public function testHandle()
    {
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $dateTime = new \DateTime();
        $scannedAt = new \DateTime();
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willReturn(12);

        $scanRepository
            ->hasScanForUserEventTypeAndObjectId(
                $user->reveal(),
                $event->reveal(),
                Type::TYPE_HAPPENING_ENTRANCE,
                12
            )->shouldBeCalled()
            ->willReturn(false)
        ;

        $scan = new User\Event\Scan(
            $event->reveal(),
            $user->reveal(),
            $scannedAt,
            $dateTime,
            Type::TYPE_HAPPENING_ENTRANCE,
            12
        );

        $scanRepository->add($scan)->shouldBeCalled();

        $handler = new ScanHappeningHandler(
            $scanRepository->reveal(),
            $dateTime
        );

        $command = new ScanHappening(
            $event->reveal(),
            $user->reveal(),
            $happening->reveal(),
            $scannedAt
        );
        $handler->handle($command);
    }
}
