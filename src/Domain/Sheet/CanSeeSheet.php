<?php

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class CanSeeSheet
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    public function __construct(RuleRepositoryInterface $ruleRepository, RequestRepositoryInterface $requestRepository)
    {
        $this->ruleRepository = $ruleRepository;
        $this->requestRepository = $requestRepository;
    }

    public function isSatisfiedBy(Sheet $fromSheet, Sheet $sheetToDisplay): bool
    {
        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($fromSheet, $sheetToDisplay);

        if (!empty($rules)) {
            return true;
        }

        return $this->requestRepository->hasRequestBetweenSheets($fromSheet, $sheetToDisplay);
    }
}
