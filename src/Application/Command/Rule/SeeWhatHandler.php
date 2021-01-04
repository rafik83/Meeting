<?php

namespace Proximum\Vimeet\Application\Command\Rule;

use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class SeeWhatHandler
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param SeeWhat $seeWhat
     */
    public function handle(SeeWhat $seeWhat)
    {
        $seeWhat->rule->update($seeWhat->seeWhat, $seeWhat->priority, $seeWhat->phoneAccessMinEvaluation, $seeWhat->emailAccessMinEvaluation, $seeWhat->requestAutomaticallyTransformedIntoMeeting);

        $this->ruleRepository->update($seeWhat->rule);
    }
}
