<?php

namespace Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
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
     * To order the requests :
     *  The most important is "who see who" rules priority.
     *  Then it's double priority (both sheets set priority)
     *  Finally, requests are ordered by their range in sheet requests list
     *       eg: Sheet A => request 1 as weight 1
     *                   => request 7 as weight 7
     *           Sheet B => request 1 as weight 1
     *                   => request 7 as weight 7
     *
     * @param \Proximum\Vimeet\Domain\Model\Event $event
     * @param Meeting\Request[]                   $approvedRequests
     *
     * @return Meeting\Request[]
     */
    private function getOrderedRequestsByPriorities(
        \Proximum\Vimeet\Domain\Model\Event $event,
        $approvedRequests
    ): array {
        $wswRules = $this->ruleRepository->getByEvent($event);

        $weightedRequests = [];
        $requestBySheetCounter = [];
        foreach ($approvedRequests as $request) {
            // 1st order weight
            $rule = $this->getWswRule(
                $wswRules,
                $request->getFromSheet()->getType(),
                $request->getToSheet()->getType()
            );

            if ($rule !== null) {
                $wswWeight = $rule->getPriority();
            } else {
                $wswWeight = 999;
            }

            // 2st order weight
            $priorityWeight = ($request->isFromPriority() && $request->isToPriority()) ? 0 : 1;

            // 3rd order weight
            $prioritizingSheet = $request->isFromPriority() ? $request->getFromSheet() : $request->getToSheet();
            if (!isset($requestBySheetCounter[$prioritizingSheet->getId()])) {
                $requestBySheetCounter[$prioritizingSheet->getId()] = 0;
            }
            $requestBySheetCounter[$prioritizingSheet->getId()]++;
            $requestCountWeight = $requestBySheetCounter[$prioritizingSheet->getId()];

            // finish
            $totalWeight = $wswWeight * 10000 + $priorityWeight * 1000 + $requestCountWeight;
            $weightedRequests[] = ['request' => $request, 'weight' => $totalWeight];
        }

        // sort by weight
        usort(
            $weightedRequests,
            static function ($weightedRequest1, $weightedRequest2) {
                return $weightedRequest1['weight'] <=> $weightedRequest2['weight'];
            }
        );

        $orderedRequests = array_map(
            static function ($weightedRequest) {
                return $weightedRequest['request'];
            },
            $weightedRequests
        );

        return $orderedRequests;
    }

    /**
     * @param Rule[] $wswRules
     * @param Type   $typeSeer
     * @param Type   $typeSeable
     *
     * @return Rule|null
     */
    private function getWswRule(array $wswRules, Type $typeSeer, Type $typeSeable)
    {
        foreach ($wswRules as $wswRule) {
            $seerOk = $wswRule->getSeer() instanceof Type
                ? $wswRule->getSeer() === $typeSeer
                : $wswRule->getSeer()->getTypes()->contains($typeSeer);

            if (!$seerOk) {
                continue;
            }

            $seableOk = $wswRule->getSeeable() instanceof Type
                ? $wswRule->getSeeable() === $typeSeable
                : $wswRule->getSeeable()->getTypes()->contains($typeSeable);

            if ($seableOk) {
                return $wswRule;
            }
        }

        return null;
    }
}
