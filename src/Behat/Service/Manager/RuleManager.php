<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RuleManager
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(RuleRepositoryInterface $ruleRepository, TypeRepositoryInterface $typeRepository)
    {
        $this->ruleRepository = $ruleRepository;
        $this->typeRepository = $typeRepository;
    }

    public function create(Type $type, Event $event): void
    {
        $rule = new Rule($event, $type, $type, []);

        $this->ruleRepository->add($rule);
    }

    public function createWith2Types(Type $type1, string $type2Name, Event $event): void
    {
        $allTypes = $this->typeRepository->getTypesByEvent($event);

        $type2 = null;
        foreach ($allTypes as $type) {
            if ($type->getTitle($event->getLocaleFallback()) === $type2Name) {
                $type2 = $type;
                break;
            }
        }
        if (!$type2) {
            throw new \InvalidArgumentException('Missing Type '.$type2Name);
        }

        $rule = new Rule($event, $type1, $type2, ['sheet_title']);

        $this->ruleRepository->add($rule);
    }
}
