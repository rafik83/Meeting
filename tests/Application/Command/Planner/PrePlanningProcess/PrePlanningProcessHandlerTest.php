<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner\PrePlanningProcess;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformApprovedRequestsByLinkedSheetsIntoMeeting;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\PrePlanningProcess;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\PrePlanningProcessHandler;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformPriorityRequestsIntoMeeting;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;

class PrePlanningProcessHandlerTest extends TestCase
{
    /**
     * TransformPriorityRequestsIntoMeetingHandler should be called before
     *      TransformApprovedRequestsByLinkedSheetsIntoMeetingHandler.
     * But it's against Prophecy philosophy to test executions order.
     *
     * See more at https://github.com/phpspec/prophecy/issues/130
     */
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $commandBus = $this->prophesize(CommandBusInterface::class);

        $commandBus
            ->handle(new TransformPriorityRequestsIntoMeeting($event->reveal(), Meeting::CREATED_BY_PLANNER))
            ->shouldBeCalled()
        ;

        $commandBus
            ->handle(new TransformApprovedRequestsByLinkedSheetsIntoMeeting($event->reveal(), Meeting::CREATED_BY_PLANNER))
            ->shouldBeCalled()
        ;

        $prePlanningProcessHandler = new PrePlanningProcessHandler($commandBus->reveal());
        $prePlanningProcessHandler->handle(
            new PrePlanningProcess($event->reveal(), Meeting::CREATED_BY_PLANNER)
        );
    }
}
