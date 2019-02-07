<?php

namespace Proximum\Vimeet\Domain\Sheet\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class CanSeeOtherSheets
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;
    
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }
    
    public function isSatisfiedBy(Sheet $sheet): bool
    {
        return null !== $this->ruleRepository->getByEventAndSeer(
            $sheet->getEvent(),
            $sheet->getType()
        );
    }
}
