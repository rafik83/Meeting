<?php

namespace Proximum\Vimeet\Domain\Participant\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\RuleRepository;

class HasAccessToCatalog
{
    /** @var RuleRepository */
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
