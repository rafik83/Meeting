<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner\PrePlanningProcess;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformIntoMeetingApprovedRequestsByLinkedSheets;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\PrePlanningProcess;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\PrePlanningProcessHandler;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\RequestAcceptedByAllLinkedSheets;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;

class PrePlanningProcessHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $commandBus = $this->prophesize(CommandBusInterface::class);
        $commandBus
            ->handle(new TransformIntoMeetingApprovedRequestsByLinkedSheets($event->reveal(), Meeting::CREATED_BY_PLANNER))
            ->shouldBeCalled()
        ;

        $prePlanningProcessHandler = new PrePlanningProcessHandler($commandBus->reveal());
        $prePlanningProcessHandler->handle(
            new PrePlanningProcess($event->reveal(), Meeting::CREATED_BY_PLANNER)
        );
    }
}
