<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateStatus;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateStatusHandler;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class UpdateStatusHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $meeting = $this->prophesize(Meeting::class);
        $status = Meeting::STATUS_CANCELED;

        $meeting->setStatus($status)->shouldBeCalled();
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->set($meeting->reveal())->shouldBeCalled();

        $handler = new UpdateStatusHandler($meetingRepository->reveal());
        $handler->handle(new UpdateStatus($meeting->reveal(), $status));
    }
}
