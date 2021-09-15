<?php

namespace Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class TransformPriorityRequestsIntoMeetingHandler
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        CommandBusInterface $commandBus,
        RequestRepositoryInterface $requestRepository,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->commandBus = $commandBus;
        $this->requestRepository = $requestRepository;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param TransformPriorityRequestsIntoMeeting $command
     *
     * @return Meeting[]
     */
    public function handle(TransformPriorityRequestsIntoMeeting $command): array
    {
        if (ExportSolutionType::SOLUTION_FROM_SCRATCH === $command->solutionType) {
            return [];
        }

        $event = $command->event;

        $approvedRequests = $this->requestRepository->findApprovedAndPrioritizedWithoutMeeting($event);

        if (empty($approvedRequests)) {
            return [];
        }

        $orderedRequests = $this->getOrderedRequestsByPriorities($event, $approvedRequests);

        $createdMeetings = [];
        foreach ($orderedRequests as $request) {
            $createdMeetings[] = $this->commandBus->handle(
                new TransformRequestIntoMeeting($request, Meeting::CREATED_BY_PLANNER, true, true)
            );
        }

        return $createdMeetings;
    }

    /**
     * @param Event             $event
     * @param Meeting\Request[] $approvedRequests
     *
     * @return Meeting\Request[]
     */
    private function getOrderedRequestsByPriorities(Event $event, array $approvedRequests): array
    {
        $wswRules = $this->ruleRepository->getByEvent($event);

        usort(
            $wswRules,
            static function (Rule $ruleA, Rule $ruleB) {
                return $ruleA->getPriority() <=> $ruleB->getPriority();
            }
        );

        $orderedRequests = [];

        foreach ($wswRules as $rule) {
            // double priority
            $toTreatRequest = [];
            foreach ($approvedRequests as $i => $approvedRequest) {
                if ($this->isRequestConcernedByRule($rule, $approvedRequest)
                    && $approvedRequest->isFromPriority() && $approvedRequest->isToPriority()) {
                    $assignedSheetId = $approvedRequest->getFromSheet()->getId();

                    $toTreatRequest[$assignedSheetId][] = $approvedRequest;
                    unset($approvedRequests[$i]);
                }
            }

            $orderedRequests = $this->sortBySheetAndPushRequests($toTreatRequest, $orderedRequests);

            // single priority
            $toTreatRequest = [];
            foreach ($approvedRequests as $i => $approvedRequest) {
                if ($this->isRequestConcernedByRule($rule, $approvedRequest)) {
                    $assignedSheetId = $approvedRequest->isFromPriority()
                        ? $approvedRequest->getFromSheet()->getId() : $approvedRequest->getToSheet()->getId();

                    $toTreatRequest[$assignedSheetId][] = $approvedRequest;
                    unset($approvedRequests[$i]);
                }
            }

            $orderedRequests = $this->sortBySheetAndPushRequests($toTreatRequest, $orderedRequests);
        }

        // not treated requests - double priority
        $toTreatRequest = [];
        foreach ($approvedRequests as $i => $approvedRequest) {
            if ($approvedRequest->isFromPriority() && $approvedRequest->isToPriority()) {
                $assignedSheetId = $approvedRequest->getFromSheet()->getId();

                $toTreatRequest[$assignedSheetId][] = $approvedRequest;
                unset($approvedRequests[$i]);
            }
        }

        $orderedRequests = $this->sortBySheetAndPushRequests($toTreatRequest, $orderedRequests);

        // not treated requests - single priority
        $toTreatRequest = [];
        foreach ($approvedRequests as $i => $approvedRequest) {
            $assignedSheetId = $approvedRequest->isFromPriority()
                ? $approvedRequest->getFromSheet()->getId() : $approvedRequest->getToSheet()->getId();

            $toTreatRequest[$assignedSheetId][] = $approvedRequest;
            unset($approvedRequests[$i]);
        }

        $orderedRequests = $this->sortBySheetAndPushRequests($toTreatRequest, $orderedRequests);

        return $orderedRequests;
    }

    private function isRequestConcernedByRule(Rule $wswRule, Meeting\Request $approvedRequest): bool
    {
        $typeSeer = $approvedRequest->getFromSheet()->getType();
        $typeSeable = $approvedRequest->getToSheet()->getType();

        $seerRule = $wswRule->getSeer();
        $seeableRule = $wswRule->getSeeable();

        return (
                $seerRule instanceof Category
                    ? in_array($typeSeer, $seerRule->getTypes(), true)
                    : $seerRule === $typeSeer
            )
            &&
            (
                $seeableRule instanceof Category
                    ? in_array($typeSeable, $seeableRule->getTypes(), true)
                    : $seeableRule === $typeSeable
            );
    }

    /**
     * @param array             $toTreatRequest
     * @param Meeting\Request[] $orderedRequests
     *
     * @return Meeting\Request[]
     */
    private function sortBySheetAndPushRequests(array $toTreatRequest, array $orderedRequests): array
    {
        while (\count($toTreatRequest)) {
            foreach ($toTreatRequest as $sheetId => $requests) {
                $orderedRequests[] = array_shift($requests);
                $toTreatRequest[$sheetId] = $requests;
                if (empty($requests)) {
                    unset($toTreatRequest[$sheetId]);
                }
            }
        }

        return $orderedRequests;
    }
}
