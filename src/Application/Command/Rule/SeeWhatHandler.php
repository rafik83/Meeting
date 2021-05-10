<?php

namespace Proximum\Vimeet\Application\Command\Rule;

use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class SeeWhatHandler
{
    private RuleRepositoryInterface $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function handle(SeeWhat $seeWhat)
    {
        $seeWhat->rule->update(
            $seeWhat->seeWhat,
            $seeWhat->priority,
            $seeWhat->phoneAccessMinEvaluation,
            $seeWhat->emailAccessMinEvaluation,
            $seeWhat->sendEmailMinEvaluation,
            $seeWhat->requestAutomaticallyTransformedIntoMeeting,
            $seeWhat->isMeetingRequestDisabled
        );

        $this->ruleRepository->update($seeWhat->rule);
    }
}
