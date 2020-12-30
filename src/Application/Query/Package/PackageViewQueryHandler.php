<?php

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Application\Query\Package\Option\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Package\Option\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Plan\PlansViewQuery;
use Proximum\Vimeet\Application\Query\Package\Plan\PlansViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\PackageView;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;

class PackageViewQueryHandler
{
    /** @var PlansViewQueryHandler */
    private $plansViewQueryHandler;

    /** @var ParticipantAndPlanningViewQueryHandler */
    private $participantAndPlanningViewQueryHandler;

    /** @var GroupsViewQueryHandler */
    private $groupsViewQueryHandler;

    /** @var AddParticipantChecker */
    private $addParticipantChecker;

    /**
     * @param PlansViewQueryHandler                  $plansViewQueryHandler
     * @param ParticipantAndPlanningViewQueryHandler $participantAndPlanningViewQueryHandler
     * @param GroupsViewQueryHandler                 $groupsViewQueryHandler
     * @param AddParticipantChecker                  $addParticipantChecker
     */
    public function __construct(
        PlansViewQueryHandler $plansViewQueryHandler,
        ParticipantAndPlanningViewQueryHandler $participantAndPlanningViewQueryHandler,
        GroupsViewQueryHandler $groupsViewQueryHandler,
        AddParticipantChecker $addParticipantChecker
    ) {
        $this->plansViewQueryHandler                  = $plansViewQueryHandler;
        $this->participantAndPlanningViewQueryHandler = $participantAndPlanningViewQueryHandler;
        $this->groupsViewQueryHandler                 = $groupsViewQueryHandler;
        $this->addParticipantChecker                  = $addParticipantChecker;
    }

    /**
     * @param PackageViewQuery $packageViewQuery
     *
     * @throws \Exception
     *
     * @return PackageView
     */
    public function handle(PackageViewQuery $packageViewQuery)
    {
        $canAddParticipant = false;

        if (Step::TYPE_PLAN === $packageViewQuery->currentStep->type) {
            $packageViewProducts = $this->plansViewQueryHandler->handle(
                new PlansViewQuery(
                    $packageViewQuery->sheet->getEvent(),
                    $packageViewQuery->sheet->getPackage(),
                    $packageViewQuery->locale
                )
            );
        } elseif (Step::TYPE_PARTICIPANT_PLANNING === $packageViewQuery->currentStep->type) {
            $packageViewProducts = $this->participantAndPlanningViewQueryHandler->handle(
                new ParticipantAndPlanningViewQuery(
                    $packageViewQuery->sheet,
                    $packageViewQuery->locale
                )
            );
            $canAddParticipant = $this->addParticipantChecker->canAddParticipant($packageViewQuery->sheet);
        } else {
            $packageViewProducts = $this->groupsViewQueryHandler->handle(
                new GroupsViewQuery(
                    $packageViewQuery->sheet,
                    $packageViewQuery->locale
                )
            );
        }

        return new PackageView(
            $packageViewProducts,
            $packageViewQuery->sheet,
            $packageViewQuery->funnel,
            $packageViewQuery->currentStep,
            $canAddParticipant
        );
    }
}
