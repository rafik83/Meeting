<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchUnavailabilityHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $begin   = new \DateTime();
        $end     = new \DateTime();
        $spotIds = [1, 2];
        $spot1 = new Spot('SP1', $event, 20.0, 1, 1, true);
        $spot2 = new Spot('SP1', $event, 20.0, 1, 1, true);

        $spots = [
            $spot1,
            $spot2,
        ];

        $meetingSlot = new MeetingSlot($event, $begin, $end);

        // Command
        $command               = new UnavailabilityBatch($spotIds, $event);
        $command->meetingSlots = [$meetingSlot];

        // Mock
        $spotRepository               = $this->prophesize(SpotRepositoryInterface::class);
        $spotUnavailabilityRepository = $this->prophesize(SpotUnavailabilityRepositoryInterface::class);
        $meetingRepository            = $this->prophesize(MeetingRepositoryInterface::class);

        $spotRepository->findMany($event, $spotIds)->shouldBeCalled()->willReturn($spots);

        $spotUnavailabilityRepository->remove($spot1)->shouldBeCalled();
        $meetingRepository->findBySpotAndSlot($spot1, $meetingSlot)->shouldBeCalled()->willReturn([]);

        $spotUnavailabilityRepository->remove($spot2)->shouldBeCalled();
        $meetingRepository->findBySpotAndSlot($spot2, $meetingSlot)->shouldBeCalled()->willReturn([]);
        $spotUnavailabilityRepository->add(new SpotUnavailability($meetingSlot, $spot2))->shouldBeCalled();

        $handler = new UnavailabilityBatchHandler(
            $spotRepository->reveal(),
            $spotUnavailabilityRepository->reveal(),
            $meetingRepository->reveal()
        );

        $result = $handler->handle($command);

        $expected = new UnavailabilityBatchResult();

        $this->assertEquals($expected, $result);
    }
}
