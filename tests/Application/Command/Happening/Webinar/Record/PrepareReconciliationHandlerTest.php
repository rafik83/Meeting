<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\Happening\Webinar\RecordJobQueueInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliation;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliationHandler;
use Proximum\Vimeet\Domain\Model\Happening;

class PrepareReconciliationHandlerTest extends TestCase
{
    public function testHandleNotWebinar(): void
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);
        $happening->getId()->shouldBeCalled()->willReturn(12);

        $jobQueue = $this->prophesize(RecordJobQueueInterface::class);
        $jobQueue->removeReconciliation(12)->shouldBeCalled();

        $command = new PrepareReconciliation($happening->reveal(), null);
        $handler = new PrepareReconciliationHandler(
            $jobQueue->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithDueDate(): void
    {
        $dueDate = new \DateTime('2022-10-10 10:00:00.000');
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $happening->getId()->shouldBeCalled()->willReturn(12);

        $jobQueue = $this->prophesize(RecordJobQueueInterface::class);
        $jobQueue->removeReconciliation(12)->shouldNotBeCalled();
        $jobQueue->prepareReconciliation(12, $dueDate);

        $command = new PrepareReconciliation($happening->reveal(), $dueDate);
        $handler = new PrepareReconciliationHandler(
            $jobQueue->reveal()
        );

        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $endDate = new \DateTime('2022-10-10 10:00:00.000');
        $endDatePlusTime = new \DateTime('2022-10-10 10:05:00.000');
        $happening = $this->prophesize(Happening::class);
        $happening->getEnd()->shouldBeCalled()->willReturn($endDate);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $happening->getId()->shouldBeCalled()->willReturn(12);

        $jobQueue = $this->prophesize(RecordJobQueueInterface::class);
        $jobQueue->removeReconciliation(12)->shouldNotBeCalled();
        $jobQueue->prepareReconciliation(12, $endDatePlusTime);

        $command = new PrepareReconciliation($happening->reveal(), null);
        $handler = new PrepareReconciliationHandler(
            $jobQueue->reveal()
        );

        $handler->handle($command);
    }
}
