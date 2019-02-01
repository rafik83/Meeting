<?php

namespace Proximum\Vimeet\Domain\Participant\Catalog;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Infrastructure\Repository\RuleRepository;

class HasAccessToCatalog
{
    /** @var RuleRepository */
    private $ruleRepository;
    
    public function __construct(RuleRepository $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }
    
    public function isSatisfiedBy(Participant $participant): bool
    {
        return null !== $this->ruleRepository->getByEventAndSeer(
            $participant->getEvent(),
            $participant->getSheet()->getType()
        );
    }
}
