<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\PrePlanningProcess;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\PrePlanningProcessHandler;
use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\Exception\Planner\NoSpotActiveException;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class ExportJobCreatorHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var PrePlanningProcessHandler */
    private $prePlanningProcessHandler;

    public function __construct(
        JobQueueInterface $jobQueue,
        PlannerJobRepositoryInterface $plannerJobRepository,
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        PrePlanningProcessHandler $prePlanningProcessHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->jobQueue = $jobQueue;
        $this->plannerJobRepository = $plannerJobRepository;
        $this->spotRepository = $spotRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->prePlanningProcessHandler = $prePlanningProcessHandler;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ExportJobCreator $exportJobCreator
     *
     * @throws NoSpotActiveException
     * @throws DayNotConfiguredException
     * @throws SlotNotConfiguredException
     */
    public function handle(ExportJobCreator $exportJobCreator)
    {
        if (false === $this->spotRepository->hasActiveSpot($exportJobCreator->event)) {
            throw new NoSpotActiveException();
        }

        if (false === $exportJobCreator->event->hasDay()) {
            throw new DayNotConfiguredException();
        }

        if (false === $this->meetingSlotRepository->hasActiveSlot($exportJobCreator->event)) {
            throw new SlotNotConfiguredException();
        }

        $plannerJob = null;

        $this->prePlanningProcessHandler->handle(
            new PrePlanningProcess($exportJobCreator->event, $exportJobCreator->solutionType)
        );

        if ($exportJobCreator->isModeAuto()) {
            $plannerJob = new PlannerJob(
                $exportJobCreator->event,
                $exportJobCreator->admin,
                $exportJobCreator->solutionType,
                $exportJobCreator->lockMeetingRequest,
                $this->dateTime
            );
            $this->plannerJobRepository->add($plannerJob);
        }

        $this->jobQueue->exportPlannerForEvent(
            $exportJobCreator->event,
            $exportJobCreator->admin,
            $exportJobCreator->locale,
            $exportJobCreator->lockMeetingRequest,
            $exportJobCreator->solutionType,
            $exportJobCreator->isModeAuto(),
            $plannerJob
        );
    }
}
