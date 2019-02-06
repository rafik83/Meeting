<?php

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class CanSeeSheet
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function isSatisfiedBy(Sheet $fromSheet, Sheet $sheetToDisplay): bool
    {
        $rules = $this->ruleRepository->getBySeerTypeAndSeeableType($fromSheet->getType(), $sheetToDisplay->getType());

        return !empty($rules);
    }
}
